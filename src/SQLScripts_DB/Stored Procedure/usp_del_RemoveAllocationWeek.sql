USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_del_RemoveAllocationWeek]    Script Date: 02/01/2026 16:38:17 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER               PROCEDURE  [dbo].[usp_del_RemoveAllocationWeek]

	-- Add the parameters for the stored procedure here
@schedulingteamid  INT,
@WeekNumber        INT,
@pNetLogin         VARCHAR(30)

AS
BEGIN	

		SET NOCOUNT ON;
		
		DECLARE  @status          VARCHAR(50),
				 @returnstring    VARCHAR(200),
				 @CurrentWeek     INT,
				 @NextWeek        INT,		
				 @vStartDate      DATE,
				 @vEndDate        DATE,
				 @vuserID         INT,
				 @AddlTeamID      INT,
				 @StartDate       VARCHAR(22),
				 @EndDate         VARCHAR(22),
				 @return_value    INT,
				 @vErrorMsg		  VARCHAR(1000),
				 @vSPStatus		  INT,
				 @AllocStatus	  INT,
				 @AllocationID	  INT,
				 @vDelAllocationID INT,
				 @vname            VARCHAR(50),
				 @HistoryTypeID    INT,	
				 @History          VARCHAR(MAX);
   
  	   DECLARE @EditAllocationStatus TABLE (SPStatus INT,ErrorMsg VARCHAR(1000));
	
	    SELECT @vuserID = UD_UserID,
			   @vname =  UD_DisplayName
		  FROM UserDetails
		 WHERE UD_NetLogin = @pNetLogin		

		SELECT @HistoryTypeID = ID
		  FROM HistoryTypes
		 WHERE HistoryType = 'PublishWeek'
		
		SELECT @CurrentWeek = MIN(ixYearWeek), 
		       @NextWeek = MAX(ixYearWeek)
          FROM TimeDimension TD
         WHERE TD.dDateTime in ( CAST(GETDATE() AS DATE) , CAST(DATEADD(WEEK,1,getdate()) AS DATE) )

        SELECT @vStartDate = Min(ddatetime),
			   @vEndDate = Max(ddatetime)
		  FROM TimeDimension
		 WHERE ixyearweek = @WeekNumber	
        
		IF ( @vStartDate IS NULL )
		 BEGIN
				SET @status = 'error'
				SET @returnstring = 'Week Number Entered is not correct!'
				SELECT @status strstatus , @returnstring strreturnstring;
				RETURN 0
		 END


		IF ( @CurrentWeek > @WeekNumber )
			BEGIN
				SET @status = 'error'
				SET @returnstring = 'You cannot delete a Week that occured before the Current Week ('
				  +SUBSTRING(CAST(@CurrentWeek AS VARCHAR), 5, 6)+'/'+ SUBSTRING(CAST(@CurrentWeek AS VARCHAR), 1, 4)+') !'
				SELECT @status strstatus , @returnstring strreturnstring;
				RETURN 0
			END
			
		IF ( @CurrentWeek = @WeekNumber or @NextWeek = @WeekNumber )
			BEGIN
				SET @status = 'error'
				SET @returnstring = 'You cannot delete the Current Week or Immediate next week!'
				SELECT @status strstatus , @returnstring strreturnstring;
				RETURN 0
			END	
			
		SELECT @AllocStatus = AL_Status,
			   @AllocationID = AL_AllocationsID
		  FROM Allocations 
		 WHERE AL_WeekNumber = @WeekNumber 
		   AND AL_SchedulingTeamID = @schedulingteamid

		IF ( ISNULL(@AllocStatus,9) = 9 )
		BEGIN
				SET @status = 'error'
				SET @returnstring = 'Week Number ['
				 +SUBSTRING(CAST(@WeekNumber AS VARCHAR), 5, 6)+'/'
				 + SUBSTRING(CAST(@WeekNumber AS VARCHAR), 1, 4)+'] has not been created yet.'
				SELECT @status strstatus , @returnstring strreturnstring;
				RETURN 0
		END
		
		IF ( @AllocStatus = 1 )
		BEGIN
				SET @status = 'error'
				SET @returnstring = 'You cannot delete a week that has been published. You must delete the published allocations before deleting the week from Allocate.'
				SELECT @status strstatus , @returnstring strreturnstring;
				RETURN 0
		END

		 BEGIN TRANSACTION
		   BEGIN TRY			
			BEGIN

			  UPDATE AP
			     SET AP.ASP_AllocationsDutyID = ADT.AD_AllocationsDutyID
			    FROM AllocationsDuties AD
			   INNER JOIN AllocationsScheduledPersons AP on ap.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
			   INNER JOIN Allocations AL on AL.AL_AllocationsID = AP.ASP_AllocationsID
			   INNER JOIN AllocationsDuties ADT on AL.AL_AllocationsID = ADT.AD_AllocationsID AND ADT.AD_iDay = AP.ASP_iDay
			   WHERE AD.AD_AllocationsID = @AllocationID
			     AND AP.ASP_AllocationsID <> @AllocationID
				 AND ADT.AD_DutyStatus = 1
				 AND ADT.AD_DutyType = 7
					
			   UPDATE Allocations
			      SET AL_Status = 9,
					  AL_UpdatedBy = @vuserID,
					  AL_UpdatedDate = GETUTCDATE()
				WHERE AL_AllocationsID = @AllocationID;

				INSERT INTO @EditAllocationStatus
				EXEC usp_Create_DutyAccPeriodSummary @WeekNumber, @WeekNumber, @schedulingteamid, @pNetLogin;

			   SET @History = 'The allocations for '+RIGHT(cast(@weekNumber as VARCHAR),2) + '/' 
				             + LEFT(cast(@weekNumber as VARCHAR),len(cast(@weekNumber as VARCHAR))-2)
							 +' were deleted on '
							 + FORMAT(Getdate(),'dd/MM/yyyy') + ' at '
							 + FORMAT(Getdate(),'HH:mm')
							 + ' by '
							 + @vname
				  
				EXEC [usp_mod_AllocationHistory] @AllocationID,@HistoryTypeID,@vuserID,@History,1; 

			END

               EXEC  @return_value = usp_CreateWTDBreach @vStartDate, 
		                         @vEndDate,
								 @schedulingteamid, 
								 @pNetLogin 
								 
				IF ( ISNULL(@return_value,0) > 0 )
				BEGIN
					THROW 51000, 'Error while creating WTD breach', 1;
				END  
		
			   
			IF ( @@TRANCOUNT  > 0 ) 
			 BEGIN
				COMMIT  TRANSACTION 
			 END

			SET @status = 'success'
			SET @returnstring = SUBSTRING(CAST(@WeekNumber AS VARCHAR), 5, 6)+'/'
								+ SUBSTRING(CAST(@WeekNumber AS VARCHAR), 1, 4)+' week removed successfully.'		

			SELECT @status strstatus , @returnstring strreturnstring

		   END TRY
			
		 BEGIN CATCH

			IF ( @@TRANCOUNT  > 0 ) 
			 BEGIN
				ROLLBACK TRANSACTION
			 END

			IF ( ERROR_NUMBER() < 50000 )
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

			SET @status = 'error'
			SET @returnstring = CASE WHEN ISNULL(@vSPStatus,1) <> 1 
						            THEN 'Unsucessfull to delete this week due to WTD breach check failure'
									ELSE 'Unsucessfull to delete this week'
									END

			SELECT @status strstatus , @returnstring strreturnstring;
			RETURN 0
			
		 END CATCH

END