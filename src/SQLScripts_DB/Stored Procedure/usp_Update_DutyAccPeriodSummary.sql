USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Update_DutyAccPeriodSummary]    Script Date: 28/11/2025 19:07:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER             PROCEDURE  [dbo].[usp_Update_DutyAccPeriodSummary]
@pDutyDate     				DATE,
@pScheduledPersonID      	INT,
@pOldDuration				INT = NULL,
@pNewDuration				INT = NULL,
@pOldOverTimeHrs			INT = NULL,
@pNewOverTimeHrs			INT = NULL,
@pNetLogin           		VARCHAR(30)
AS

BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;
    
   DECLARE    @vuserID        INT;
  
   select @vuserID = UD_UserID 
     from [UserDetails]  (NOLOCK)
    where UD_NetLogin = @pNetLogin  
  
     BEGIN TRY
        BEGIN TRANSACTION  

			 UPDATE AccPeriodDutySummary
			    SET APD_AccPeriodDuration = APD_AccPeriodDuration - ISNULL(@pOldDuration,0) + ISNULL(@pNewDuration,0),
					APD_AccPeriodDays = CASE WHEN (ISNULL(@pOldDuration,0) = ISNULL(@pNewDuration,0)
													OR ( ISNULL(@pOldDuration,0) > 0 AND ISNULL(@pNewDuration,0) > 0 ))
											 THEN APD_AccPeriodDays
											 WHEN ISNULL(@pOldDuration,0) = 0 AND ISNULL(@pNewDuration,0) > 0
											 THEN APD_AccPeriodDays + 1
											 WHEN ISNULL(@pOldDuration,0) > 0 AND ISNULL(@pNewDuration,0) = 0
												  AND APD_AccPeriodDays > 0
											 THEN APD_AccPeriodDays - 1
										 END,
					APD_TotalOverTimeHRS = APD_TotalOverTimeHRS - ISNULL(@pOldOverTimeHrs,0) + ISNULL(@pNewOverTimeHrs,0),
					APD_UpdatedBy = @vuserID,
					APD_UpdatedDate = getutcdate()
			  WHERE APD_ScheduledPersonID = @pScheduledPersonID
			    AND @pDutyDate between APD_AccPeriodStartDate AND APD_AccPeriodEndDate;

		  IF ( @@ROWCOUNT = 0 )
		    BEGIN

				INSERT INTO AccPeriodDutySummary (
						APD_ScheduledPersonID,
						APD_AccPeriodStartDate,
						APD_AccPeriodEndDate,
						APD_AccPeriodStartWeek,
						APD_AccPeriodEndWeek,
						APD_AccPeriodDuration,
						APD_AccPeriodDays,
						APD_TotalOverTimeHRS,
						APD_CreatedBy,
						APD_CreatedDate)
				SELECT distinct sp.UD_UserID,
					   AGD.AccPeriodStartDate as startdate , 
					   AGD.AccPeriodEndDate as enddate, 
					   AGD.AccPeriodStartWeek as startweek, 
					   AGD.AccPeriodEndWeek as endweek,
					   @pNewDuration as totalduration,
					   1 as NoOfDays,
					   0 as OverTimeHrs,
					   @vuserID,
					   GETDATE()
				  FROM UserDetails AS sp (nolock) 
				 INNER JOIN UserConfigs SCP ON  SCP.UC_UserID = sp.UD_UserID		 
				 INNER JOIN REF_AccountingGroup_Dates AGD ON SCP.UC_AccGroupID=AGD.AccGroupID
				 INNER join Timedimension TD on AGD.BBCWeek = TD.ixYearWeek 
				 WHERE TD.dDateTime between  SCP.UC_StartDate and SCP.UC_EndDate 
				   AND TD.dDateTime between isnull( AGD.AccPeriodStartDate, TD.dDateTime) and isnull( AGD.AccPeriodEndDate, TD.dDateTime ) 
				   AND td.dDateTime = @pDutyDate
				   AND sp.UD_UserID = @pScheduledPersonID

				  IF ( @@ROWCOUNT = 0 )
					BEGIN

						INSERT INTO AccPeriodDutySummary (
								APD_ScheduledPersonID,
								APD_AccPeriodStartDate,
								APD_AccPeriodEndDate,
								APD_AccPeriodStartWeek,
								APD_AccPeriodEndWeek,
								APD_AccPeriodDuration,
								APD_AccPeriodDays,
								APD_TotalOverTimeHRS,
								APD_CreatedBy,
								APD_CreatedDate)
						SELECT distinct sp.UD_UserID,
								AccPeriodStartDate as startdate , 
								AccPeriodEndDate as enddate, 
								AccPeriodStartWeek as startweek, 
								AccPeriodEndWeek as endweek,
								@pNewDuration as totalduration,
								1 as NoOfDays,
								0 as OverTimeHrs,
								@vuserID,
								GETDATE()
							FROM UserDetails AS sp (nolock) 
							INNER join ( select min(TD2.dDateTime) AccPeriodStartDate,
												max(TD2.dDateTime) AccPeriodEndDate,
												TD2.ixYearWeek AccPeriodStartWeek,
												TD2.ixYearWeek AccPeriodEndWeek
										   from Timedimension TD1
										   INNER JOIN Timedimension TD2 on TD2.ixYearWeek = TD1.ixYearWeek
										  where TD1.dDateTime = @pDutyDate
										  group by TD2.ixYearWeek) TD on 1 = 1
							WHERE SP.UD_UserID = @pScheduledPersonID

					END

			END

		 -- Create ROTA duration Summary End

     
        IF ( @@TRANCOUNT  > 0 ) 
         BEGIN
           COMMIT  TRANSACTION 
         END       

		SELECT 0 as SPExecStatus,
			   'Success' as SPMessage           

     END TRY
        
        BEGIN CATCH
          
          IF ( @@TRANCOUNT  > 0 ) 
           BEGIN		  
             ROLLBACK  TRANSACTION
		   END

		 INSERT INTO ErrorLog
		        (ErrorNumber,
				 ErrorState,
				 ErrorSeverity,
				 ErrorProcedure,
				 ErrorLine,
				 ErrorMessage,
				 ErrorDateTime,
				 UserName
				)
         SELECT ERROR_NUMBER() AS ErrorNumber,
                ERROR_STATE() AS ErrorState,
				ERROR_SEVERITY() AS ErrorSeverity,
				ERROR_PROCEDURE() AS ErrorProcedure,
				ERROR_LINE() AS ErrorLine,
				ERROR_MESSAGE() AS ErrorMessage,
				getutcdate(),
				@vuserID		  

		 SELECT @@IDENTITY as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage  
          
        END CATCH;
        
END