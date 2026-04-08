USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Create_Allocations_From_Rota]    Script Date: 24/03/2026 15:08:45 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER             PROCEDURE  [dbo].[usp_Create_Allocations_From_Rota]
@AllocationsSPID        INT,
@ScheduledPersonID		INT,
@DutyDate				DATE,
@IsCreateDutyFromRota   INT,
@NetLogin               VARCHAR(30) 
AS
BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;
    
	   DECLARE  @vname               VARCHAR(50),
				@vuserID             INT,
				@WeekNumber          INT,
				@iDay                INT,
				@TeamID              INT,
				@return_value        INT,
				@RowCount            INT = 0,
				@DisplayName		 VARCHAR(50),
				@ReturnValue		 INT;

    DECLARE     @DutyName NVARCHAR(100), 
				@Duration FLOAT, 
				@StartTime FLOAT, 
				@EndTime FLOAT,
				@BackColour BIGINT, 
				@FontColour BIGINT, 
				@StartDate DATETIME, 
				@EndDate DATETIME, 
				@SortCode NVARCHAR(30),
				@aftermidnight INT, 
				@dutyProgramId INT, 
				@dutyProgramId2 INT, 
				@dutyProgramId3 INT, 
				@dutyProgramId4 INT, 
				@dutyProgramId5 INT, 
				@dutyProgramId6 INT, 
				@dutyBreakTime INT,
				@dutyColorId INT, 
				@MasterDutyId INT,  
				@IsNeedCovering BIT,
				@DutyTypeID INT,
				@IsOverrideOver12 BIT,
				@AllocationDutyID INT,
				@AllocationID		INT,
				@OldDuration  INT = 0,
				@OldOverTimeHrs INT = 0;

	 DECLARE @EditAllocationStatus TABLE (SPStatus INT,ErrorMsg VARCHAR(1000));


      SELECT @vname = UD_DisplayName,
			 @vuserID = UD_UserID
	    FROM UserDetails  (nolock)
	   WHERE UD_NetLogin = @NetLogin

      SELECT @DisplayName = UD_DisplayName
	    FROM UserDetails  (nolock)
	   WHERE UD_UserID = @ScheduledPersonID

	   
     IF (ISNULL(@AllocationsSPID,0) > 0 )
	  BEGIN

	   SELECT @WeekNumber = AL_WeekNumber,
			  @iDay = ASP_iDay,
			  @TeamID = AL_SchedulingTeamID,
			  @ScheduledPersonID = ASP_SchedulingPersonID,
			  @AllocationID = AL_AllocationsID,
			  @OldDuration = ISNULL(AD_Duration,0),
			  @OldOverTimeHrs = ISNULL(ASP_OverTimeHours,0)
		 FROM Allocations AL
		INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID
		INNER JOIN AllocationsDuties AD ON AD.AD_AllocationsDutyID = ASP.ASP_AllocationsDutyID
		WHERE ASP_AllocationsSPID = @AllocationsSPID

	   END
	  ELSE
	   BEGIN

	     SELECT  @WeekNumber = TD.ixYearWeek,
				  @iDay = TD.ixDayInWeek,
				  @OldDuration = 0,
				  @OldOverTimeHrs = 0
		  FROM TimeDimension TD
		 WHERE dDateTime = @DutyDate

		 SELECT @TeamID = TeamID
		   FROM ScheduledPersonTeam_LINK
		  WHERE ScheduledPersonID = @ScheduledPersonID
		    AND scheduledType = 1
			AND IsHomeTeam = 1
			AND @DutyDate between StartDate and EndDate

		 SELECT @AllocationID = AL_AllocationsID
		   FROM Allocations
		  WHERE AL_WeekNumber = @WeekNumber
		    AND AL_SchedulingTeamID = @TeamID

	   END
	 


    BEGIN TRY
        BEGIN TRANSACTION  

          -- Create week from Rota and schedule person list

	  SELECT    @DutyName = FD.dutyname,
				@Duration = FD.duration,
				@StartTime = FD.starttime,
				@EndTime = FD.endtime,
				@DutyDate = FD.DutyDate,
				@BackColour  = FD.backcolour,
				@FontColour = FD.fontcolour,
				@StartDate = FD.startdate,
				@EndDate = FD.enddate,
				@SortCode = FD.SortCode,
				@aftermidnight = CASE WHEN DATEDIFF(DAY,@StartDate,@EndDate) = 1
									  THEN 1
									  ELSE 0
									  End,
				@dutyProgramId = FD.dutyprogramid,
				@dutyProgramId2 = FD.DutyProgramId2,
				@dutyProgramId3 = FD.DutyProgramId3, 
				@dutyProgramId4 = FD.DutyProgramId4,
				@dutyProgramId5 = FD.DutyProgramId5, 
				@dutyProgramId6 = FD.DutyProgramId6, 
				@dutyBreakTime  = FD.dutybreaktime,
				@dutyColorId = FD.DutyColourID, 
				@MasterDutyId = FD.masterdutyid,  
				@IsNeedCovering = FD.IsNeedCovering,
				@IsOverrideOver12 = FD.IsOverrideOver12,
				@DutyTypeID = FD.DutyTypeID
		   FROM ufn_get_SchedulingTeamROTA(@WeekNumber,@TeamID) FD
		   WHERE FD.SchedulingPersonID = @ScheduledPersonID
		     AND FD.DutyDate = @DutyDate

        IF ( @IsCreateDutyFromRota = 1 and @DutyTypeID is not null or ( @IsCreateDutyFromRota = 0 AND @DutyTypeID <> 1 ))
		 BEGIN



			   EXEC @ReturnValue =  usp_CreateDuty	@AllocationID,
									0,
									@ScheduledPersonID,
									@DutyName,
									@DutyDate,
									@StartTime,
									@EndTime,
									@dutyBreakTime,
									@Duration,
									@dutyColorId,
									@dutyProgramId,
									@dutyProgramId2,
									@dutyProgramId3,
									@dutyProgramId4,
									@dutyProgramId5,
									@dutyProgramId6,
									0,
									@IsNeedCovering,
									@IsOverrideOver12,
									NULL,
									@NetLogin,
									@DutyTypeID,
									0,0,
									@MasterDutyId = @MasterDutyId,
									@IsCreateHistory = 0,
									@AllocationsDutyID = @AllocationDutyID OUTPUT


					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating duty', 1;
					END  


				IF ( ISNULL(@AllocationsSPID,0) = 0 )
				 BEGIN
					
					EXEC @ReturnValue = usp_CreateScheduledPerson  @AllocationID,
													@AllocationDutyID,
													@ScheduledPersonID,
													@DutyDate,
													@NetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT



					IF ( ISNULL(@ReturnValue,0) <> 0 )
					BEGIN
						THROW 51001, 'Error while creating Allocation for Person', 1;
					END  
				 END

				 -- Commenting as this is not in the current code
				 /*	SELECT @IsDutyOverLap = IsDutyOverLap,
						   @prevOverlapMsg = PrevDayOvrlapMsg,
						   @nextOverlapMsg = NextDayOvrlapMsg
					  FROM ufn_check_DutyOverLap(@AllocationsSPID, @vStartdate,@vEndDate)

					SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

					IF ( @IsDutyOverLap = 1 )
					 BEGIN
					  THROW 51001, @OvreLapMsg, 1;
					 END; */

			    UPDATE AllocationsScheduledPersons
				   SET  ASP_AllocationsDutyID = @AllocationDutyID,
						ASP_OverTwelveStatus = case when @Duration > 43200
						                        and isnull(@IsOverrideOver12,1) = 1
						                        then 1 else 0 end,
						ASP_OverTwelveHrs = 0,
						ASP_IsOverseasOverTwelve = 0,
						ASP_UpdatedBy = @vuserID,
						ASP_UpdatedDate = GETUTCDATE()
				WHERE ASP_AllocationsSPID = @AllocationsSPID


				INSERT INTO @EditAllocationStatus
				EXEC usp_Update_DutyAccPeriodSummary @DutyDate, @ScheduledPersonID,
										@OldDuration,@Duration,
										@OldOverTimeHrs,0,@NetLogin
			
				EXEC @ReturnValue = usp_CreateAllocationsUpdate NULL,@AllocationDutyID,
												 @AllocationsSPID,
												 0,
												 @NetLogin;

				IF ( ISNULL(@ReturnValue,0) <> 0 )
				BEGIN
					THROW 51000, 'Error while updating Allocations update table', 1;
				END;		

				SET @RowCount = 1
  
		END


		  IF ( @AllocationDutyID > 0 )
		   BEGIN

           -- Create assigned job start
 
			INSERT INTO AllocationsJobs
				  ( AJ_AllocationsDutyID,
					AJ_MasterJobID,
					AJ_ProgrammeID,
					AJ_Contact,
					AJ_Location,
					AJ_JobName,
					AJ_JobStartTimeSec,
					AJ_JobEndTimeSec,
					AJ_JobStartTimeUTC,
					AJ_JobEndTimeUTC,
					AJ_JobStartTimeLocal,
					AJ_JobEndTimeLocal,
					AJ_JobBGColour,
					AJ_JobFontColour,
					AJ_Comments,
					AJ_JobStatus,
					AJ_JobInfo,
					AJ_CreatedBy,
					AJ_CreatedDate)
			SELECT AD.AD_AllocationsDutyID,
				   MJ.masterjobid,
				   PG.ID as programmeid,
				   mj.Contact,
				   mj.[Location],
				   MJ.JobName,
				   case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end as starttime,
				   case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end as endtime,
				   CASE
					 WHEN MJ.starttime = 0 THEN AD.AD_DutyDate
					 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AD.AD_DutyDate)
					 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.starttime - 86400 )
					 ELSE dbo.ufn_ConvertToDateTime(AD_DutyDate,MJ.starttime ) END AS StartDate,
				   CASE
					 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AD.AD_DutyDate)
					 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.EndTime - 86400 )
					 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AD.AD_DutyDate)
					 ELSE dbo.ufn_ConvertToDateTime(AD.AD_DutyDate,MJ.EndTime) END AS EndDate,	
				   CASE
					 WHEN MJ.starttime = 0 THEN AD.AD_DutyDate
					 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AD.AD_DutyDate)
					 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.starttime - 86400 )
					 ELSE dbo.ufn_ConvertToDateTime(AD_DutyDate,MJ.starttime ) END AS StartDateLocal,
				    CASE
					 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AD.AD_DutyDate)
					 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.EndTime - 86400)
					 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AD.AD_DutyDate)
					 ELSE dbo.ufn_ConvertToDateTime(AD.AD_DutyDate,MJ.EndTime) END AS EndDateLocal,						 
				   JC.ColourBackground as jobbackcolour,
				   JC.ColourFont as jobfontcolour,
				   NULL,
				   1,				   
				   mj.Details as job_info,
				   @vuserID,
				   getdate()
			FROM MasterJobs MJ (NOLOCK)
			  INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
			  INNER JOIN AllocationsDuties AD on AD_MasterDutyID = mdmj.MasterDutyID    
			  LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
			  LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
			  LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
			WHERE MJ.IsActive=1 
			  and MJ.TeamID = @teamId
			  and AD_AllocationsDutyID = @AllocationDutyID   
   
        -- Create assigned job End
   
            
        -- Create Allocation history Start

          INSERT INTO history
                (
                  historytype,
                  attributeid,
                  datetime,
                  userid,
                  history,
				  HistorySubType
                )   
          SELECT ht.id AS historytype,
				 @AllocationDutyID AS attributeid,
				   getdate(),
				   @vuserID,
				   'Created '
				   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
				   + ' By ' + @vname 
				   +  ' assigned to '+ @DisplayName
				   + '. Duty: ' + @DutyName 		   
				   +'.',			   
				   'DH'
          FROM   HistoryTypes ht
         WHERE  historytype = 'AllocationDuty'

          INSERT INTO history
                (
                  historytype,
                  attributeid,
                  datetime,
                  userid,
                  history,
				  HistorySubType
                )   
          SELECT ht.id AS historytype,
				 @AllocationsSPID AS attributeid,
				 getdate(),
				 @vuserID,
				 'Created '
				   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
				   + ' By ' + @vname 
				   +  ' assigned to '+ @DisplayName
				   + '. Duty: ' + @DutyName 		   
				   +'.',			   
				 'PH'
          FROM  HistoryTypes ht
         WHERE  historytype = 'AllocationScheduledPerson'
        
        -- Create Allocation history End
    
        -- Create Job history Start   

        INSERT INTO history
              (
                    historytype,
                    attributeid,
                    datetime,
                    userid,
                    history
              )
        SELECT ht.id AS historytype,
               aj.AJ_AllocateJobID AS attributeid,
               getdate(),
               @vuserID,
               'New Job created by '+@vname+' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +'.'
        FROM   AllocationsJobs aj
        INNER JOIN AllocationsDuties al ON al.AD_AllocationsDutyID = AJ_AllocationsDutyID
        INNER JOIN historytypes ht on 1 =1 
        WHERE AL.AD_AllocationsDutyID = @AllocationDutyID
		  AND historytype='AllocationJobs'
  
        -- Create Job history End 
        
		-- WTD Verification Start
        
           BEGIN                
			EXEC @return_value = usp_UpdateWTD @AllocationsSPID, @NetLogin          
           END 

	   
         END
        
		-- WTD Verification end 

         IF ( @@TRANCOUNT  > 0 ) 
			 BEGIN
			   COMMIT  TRANSACTION 
			 END   

          
		  IF ( isnull(@RowCount,0) > 0 )
		   BEGIN
            SELECT 0 as SPExecStatus,'Success' as SPMessage 
		   END
         
		  IF ( ISNULL(@RowCount,0) = 0 and @DutyTypeID IS NULL )
		   BEGIN
		    SELECT 1 as SPStatus, 'Failed - No ROTA available ' as SPMessage
		   END
          ELSE
		   BEGIN
		    IF ( ISNULL(@RowCount,0)= 0 and @IsCreateDutyFromRota = 0 AND isnull(@DutyTypeID,0) = 1 )	
		     BEGIN
		      SELECT 2 as SPStatus, 'Failed - Cannot create Master Duty from ROTA' as SPMessage
		     END		  
		   END
           			
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

	 	 SELECT ISNULL(@@IDENTITY,ERROR_NUMBER()) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage 
          
        END CATCH;
        
END