USE [Allocate7_REMEDIATE]
GO
/****** Object:  StoredProcedure [dbo].[usp_MoveJobToOtherDuty]    Script Date: 26/06/2025 22:41:22 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
create   PROCEDURE [dbo].[usp_MoveJobToOtherDuty_Unused]
@SchedulingPersonID		INT = NULL,
@DutyDate				DATE,
@TeamID					INT,
@AllocateJobID			INT,
@AllocationsDutyID		INT = NULL,
@JobStartTime			INT,
@JobEndTime				INT,
@IsAfterMidNightJob		BIT = 0,
@UserID					INT,
@Role					INT

AS
BEGIN

 SET NOCOUNT ON;

  DECLARE @Allocationsid			INT,
		  @JobName					NVARCHAR(100),
          @JobStartTimeSec			INT,
		  @JobEndTimeSec			INT,
		  @WeekNumber				INT,
		  @iDay						INT,
          @DutyHistory1				VARCHAR(Max),
		  @DutyHistory2				VARCHAR(Max),
		  @JobHistory				VARCHAR(Max),
		  @DutyStartTimeSec			INT,
		  @DutyEndTimeSec			INT,
		  @NewDutyStartTimeSec		INT,
		  @NewDutyEndTimeSec		INT,
		  @ReturnValue				INT = 0,
		  @DutyName					NVARCHAR(100),
		  @NetLogin					NVARCHAR(25),
		  @Status					INT ,
		  @ReturnString				VARCHAR(1000),
		  @WTDFlag					BIT = 0,
		  @DisplayName				NVARCHAR(100),
		  @UpdateAllocationFlag		BIT = 1,
		  @Name						VARCHAR(50),
		  @DutyType					INT,
		  @JobStartTimeLocal		DATETIME,
		  @JobEndTimeLocal			DATETIME,
		  @DutyStartTimeLocal		DATETIME,
		  @DutyEndTimeLocal			DATETIME,
		  @NewDutyStartTimeLocal	DATETIME,
		  @NewDutyEndTimeLocal		DATETIME,
		  @DutyHistoryType			INT,
		  @PersonHistoryType		INT,
		  @JobHistoryType			INT,
		  @AllocationsSPID			INT,
		  @IsDutyOverLap			BIT,
		  @FromAllocationsDutyID	INT,
		  @prevOverlapMsg			VARCHAR(500),
		  @nextOverlapMsg			VARCHAR(500),
		  @OvreLapMsg				VARCHAR(1000);

    SELECT @NetLogin = UD_NetLogin ,
		   @Name = UD_DisplayName
	  FROM UserDetails
     WHERE UD_UserID = @UserID
	
		SET @status = 0;
		SET @returnstring = '';

	SELECT @DutyHistoryType = ID
	 FROM HistoryTypes
	WHERE HistoryType = 'AllocationDuty'

	SELECT @PersonHistoryType = ID
	 FROM HistoryTypes
	WHERE HistoryType = 'AllocationScheduledPerson'

	SELECT @JobHistoryType = ID
	 FROM HistoryTypes
	WHERE HistoryType = 'AllocationJobs'
	
	 SELECT @jobname=AJ_JobName,
			@JobStartTimeSec = AJ_JobStartTimeSec,
			@JobEndTimeSec = AJ_JobEndTimeSec,
			@JobStartTimeLocal = AJ_JobStartTimeLocal,
			@JobEndTimeLocal = AJ_JobEndTimeLocal,
			@FromAllocationsDutyID = AJ_AllocationsDutyID
	   FROM AllocationsJobs (NOLOCK) 
	  where AJ_AllocateJobID = @AllocateJobID;

        IF ( @FromAllocationsDutyID IS NULL )
		  BEGIN
		    SELECT 1 AS SPExecStatus,
				   'The Job Does Not Exists.' AS SPMessage	
		  END

		SET @JobHistory = ''

		IF ( ISNULL(@JobStartTime,0) <> @JobStartTimeSec )
		 BEGIN
	
			  SET @JobHistory = 'Job Start Time amended by '+@Name+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From '+right('0'+CAST( isnull(case when @JobStartTimeSec = 86400 then 0 else @JobStartTimeSec end ,0) / 3600 AS varchar(2)),2) + ':'  
							 + right('0' + CAST((isnull(case when @JobStartTimeSec = 86400 then 0 else @JobStartTimeSec end ,0) % 3600)/60 AS varchar(2)),2)+' To '
							 + right('0'+CAST( isnull(case when @JobStartTime = 86400 then 0 else @JobStartTime end ,0) / 3600 AS varchar(2)),2) + ':'  
							 + right('0' + CAST((isnull(case when @JobStartTime = 86400 then 0 else @JobStartTime end ,0) % 3600)/60 AS varchar(2)),2)+'.'	
			  
			  SET @JobStartTimeSec = @JobStartTime

			  SET @JobStartTimeLocal =	  CASE  WHEN @JobStartTimeSec = 0 and @IsAfterMidNightJob = 0 
												THEN @DutyDate
												WHEN @JobStartTimeSec = 0 and @IsAfterMidNightJob = 1 
												THEN DATEADD(DAY,1,@DutyDate)
												WHEN @JobStartTimeSec = 86400 
												THEN DATEADD(DAY,1,@DutyDate)
												WHEN @JobStartTimeSec > 86400 
												THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@JobStartTimeSec )
												WHEN @JobStartTimeSec < @JobStartTime and @IsAfterMidNightJob = 1 
												THEN  dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@JobStartTimeSec )
												ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@JobStartTimeSec ) END 
			 	
			  SET @NewDutyStartTimeSec = case when @jobstartTime%3600/60 > 0 and  @jobstartTime%3600/60 < 15 THEN @jobstartTime - (@jobstartTime%3600) 
										  when @jobstartTime%3600/60 > 15 and  @jobstartTime%3600/60 < 30 THEN @jobstartTime - (@jobstartTime%3600) + 900
										  when @jobstartTime%3600/60 > 30 and  @jobstartTime%3600/60 < 45 THEN @jobstartTime - (@jobstartTime%3600) + 1800
										  when @jobstartTime%3600/60 > 45  THEN @jobstartTime - (@jobstartTime%3600) + 2700
										  ELSE @jobstartTime
									   end

			  SET @NewDutyStartTimeSec = CASE WHEN @NewDutyStartTimeSec >= 86400 then ( @NewDutyStartTimeSec - 86400) else @NewDutyStartTimeSec end

			  SET @JobStartTimeSec = CASE WHEN @JobStartTime >= 86400 THEN @JobStartTime - 86400 ELSE @JobStartTime END;
		 
		 END


		IF ( ISNULL(@JobEndTime,0) <> @JobEndTimeSec )
		 BEGIN

		   SET @JobHistory = @JobHistory+CHAR(13)
						 +'Job End Time amended by '+@Name+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						 +' From '+right('0'+CAST( isnull(case when @JobEndTimeSec = 86400 then 0 else @JobEndTimeSec end,0) / 3600 AS varchar(2)),2) + ':'  
						 + right('0' + CAST((isnull(case when @JobEndTimeSec = 86400 then 0 else @JobEndTimeSec end,0) % 3600)/60 AS varchar(2)),2)+' To '
						 + right('0'+CAST( isnull(case when @JobEndTime = 86400 then 0 else @JobEndTime end,0) / 3600 AS varchar(2)),2) + ':'  
						 + right('0' + CAST((isnull(case when @JobEndTime = 86400 then 0 else @JobEndTime end,0) % 3600)/60 AS varchar(2)),2)+'.'

			SET @JobEndTimeSec =  @JobEndTime	

			SET @JobEndTimeLocal = CASE WHEN @JobStartTimeSec > 0 AND @JobEndTimeSec = 0 THEN DATEADD(DAY,1,@DutyDate)
										WHEN @IsAfterMidNightJob = 1 THEN DATEADD(DAY,1,@DutyDate)
										WHEN @JobEndTimeSec > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@JobEndTimeSec)
										WHEN @JobEndTimeSec = 86400 THEN DATEADD(DAY,1,@DutyDate)
										ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@JobEndTimeSec) END 
			
			SET @NewDutyEndTimeSec =	case when @JobEndTime%3600/60 > 0 and  @JobEndTime%3600/60 < 15 THEN @JobEndTime - (@JobEndTime%3600) + 900
										 when @JobEndTime%3600/60 > 15 and  @JobEndTime%3600/60 < 30 THEN @JobEndTime - (@JobEndTime%3600) + 1800
										 when @JobEndTime%3600/60 > 30 and  @JobEndTime%3600/60 < 45 THEN @JobEndTime - (@JobEndTime%3600) + 2700
										 when @JobEndTime%3600/60 > 45  THEN @JobEndTime - (@JobEndTime%3600) + 3600
										 ELSE @JobEndTime
									  end
								  
			SET @NewDutyEndTimeSec = CASE WHEN @NewDutyEndTimeSec >= 86400 then ( @NewDutyEndTimeSec - 86400) else @NewDutyEndTimeSec end 

			SET @JobEndTimeSec =  CASE WHEN @JobEndTime	>= 86400 THEN @JobEndTime - 86400 ELSE @JobEndTime END
			
		 END
		  
 













	 IF ( ISNULL(@AllocationsDutyID,0) > 0 )
	  BEGIN
			IF EXISTS ( SELECT 1
						  FROM AllocationsJobs
						 WHERE AJ_AllocationsDutyID = @AllocationsDutyID
						  AND AJ_JobStatus <> 9
						  AND AJ_JobStartTimeLocal < @JobEndTimeLocal 
						  AND AJ_JobEndTimeLocal > @JobStartTimeLocal)

		BEGIN
		 SELECT 1 AS SPExecStatus,
				'The Job you are trying to add will conflict with existing job. Please Try Again.' AS SPMessage		        
		 RETURN;
		END
	  END
 

	BEGIN TRY
	  BEGIN TRANSACTION

		IF ( ISNULL(@AllocationsDutyID,0) = 0 )
		  BEGIN
		  
			  SELECT @AllocationsDutyID = AD_AllocationsDutyID 
			    FROM Allocations (NOLOCK) AL 
			   INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
			   INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
											  AND TD.ixDayInWeek = AD_iDay
			   WHERE TD.dDateTime = @DutyDate
				 AND AL_SchedulingTeamID = @teamId 
				 AND AD_DutyType = 7
		   

                SET @JobHistory = @JobHistory 
								  +'This Job ( '+ @JobName + ' ) was Moved to Unallocated ' + ' by '+ @Name + ' On '
							      + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+ '<hr>';			

		  END
		ELSE
		  BEGIN
		    
			 SELECT @DutyName			= AD_DutyName,
					@DutyType			= AD_DutyType,
					@DutyDate			= AD_DutyDate,
					@DutyStartTimeSec	= AD_StartTimeSec,
					@DutyEndTimeSec		= AD_EndTimeSec,
					@DutyStartTimeLocal	= AD_DutyStartTimeLocal,
					@DutyEndTimeLocal	= AD_DutyEndTimeLocal,
					@SchedulingPersonID	= UD_UserID,
					@DisplayName		= UD_DisplayName,
					@AllocationsSPID	= ASP_AllocationsSPID
			  FROM AllocationsDuties AD
			  LEFT JOIN AllocationsScheduledPersons ASP on ASP_AllocationsDutyID = AD_AllocationsDutyID
			  LEFT JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
			 WHERE AD_AllocationsDutyID = @AllocationsDutyID;	  
		      
			 IF ( @DutyStartTimeLocal > @JobStartTimeLocal OR @DutyEndTimeLocal < @JobEndTimeLocal )
			   BEGIN
				IF (@AllocationsSPID > 0 )
				  BEGIN
				   IF ( @DutyStartTimeLocal > @JobStartTimeLocal AND @DutyEndTimeLocal < @JobEndTimeLocal )
				     BEGIN

				   			SELECT @IsDutyOverLap = IsDutyOverLap,
								   @prevOverlapMsg = PrevDayOvrlapMsg,
								   @nextOverlapMsg = NextDayOvrlapMsg
							  FROM ufn_check_DutyOverLap(@AllocationsSPID, @JobStartTimeLocal,@JobEndTimeLocal)

							SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

							IF ( @IsDutyOverLap = 1 )
							 BEGIN
							  THROW 51001, @OvreLapMsg, 1;
							 END;

				     END
				   IF ( @DutyStartTimeLocal > @JobStartTimeLocal AND @DutyEndTimeLocal > @JobEndTimeLocal )
				     BEGIN

				   			SELECT @IsDutyOverLap = IsDutyOverLap,
								   @prevOverlapMsg = PrevDayOvrlapMsg
							  FROM ufn_check_DutyOverLap_PrevDay(@AllocationsSPID, @JobStartTimeLocal)							

							IF ( @IsDutyOverLap = 1 )
							 BEGIN
							  THROW 51002, @prevOverlapMsg, 1;
							 END;							

				     END
				   IF ( @DutyStartTimeLocal < @JobStartTimeLocal AND @DutyEndTimeLocal < @JobEndTimeLocal )
				     BEGIN

				   			SELECT @IsDutyOverLap = IsDutyOverLap,
								   @nextOverlapMsg = NextDayOvrlapMsg
							  FROM ufn_check_DutyOverLap_NextDay(@AllocationsSPID, @JobStartTimeLocal)							

							IF ( @IsDutyOverLap = 1 )
							 BEGIN
							  THROW 51003, @nextOverlapMsg, 1;
							 END;
				     END
				   END

				   		SET @DutyHistory1 ='Duty start time update because of Move job start time by ' 
									  + @Name
									  +' From '+right('0'+CAST( isnull(@DutyStartTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@DutyStartTimeSec,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(@JobStartTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@JobStartTimeSec,0) % 3600)/60 AS varchar(2)),2)								  
									  +' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				   		SET @DutyHistory2 = ' Duty end time update because to Move job end time by ' + @Name 
									  + ' From '+right('0'+CAST( isnull(@DutyEndTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@DutyEndTimeSec,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(@JobEndTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@JobEndTimeSec,0) % 3600)/60 AS varchar(2)),2)										  
									  + ' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				 IF ( @DutyStartTimeLocal > @JobStartTimeLocal AND @DutyEndTimeLocal < @JobEndTimeLocal )
				  BEGIN
				    
					SET @DutyStartTimeSec	= @JobStartTimeSec
					SET @DutyEndTimeSec		= @JobEndTimeSec
					SET @DutyStartTimeLocal	= @JobStartTimeLocal
					SET @DutyEndTimeLocal	= @JobEndTimeLocal
				    SET @DutyHistory2 = @DutyHistory1 + CHAR(13) + @DutyHistory2

				  END
				    
				 IF ( @DutyStartTimeLocal > @JobStartTimeLocal AND @DutyEndTimeLocal > @JobEndTimeLocal )
				  BEGIN

					SET @DutyStartTimeSec	= @JobStartTimeSec
					SET @DutyStartTimeLocal	= @JobStartTimeLocal
					SET @DutyHistory2 = @DutyHistory1 

				  END
				 
				 IF ( @DutyStartTimeLocal < @JobStartTimeLocal AND @DutyEndTimeLocal < @JobEndTimeLocal )
				  BEGIN

					SET @DutyEndTimeSec		= @JobEndTimeSec
					SET @DutyEndTimeLocal	= @JobEndTimeLocal

				  END
 
				 EXEC @ReturnValue = usp_UpdateDuty @AllocationsDutyID,@DutyStartTimeSec, 
													@DutyEndTimeSec, @DutyStartTimeLocal, 
													@DutyEndTimeLocal,@UserID,@Role

					IF ( @ReturnValue > 0 )
					 BEGIN
					   THROW 51004, 'Error while updating duty details', 1;
					 END;

				 EXEC [usp_mod_AllocationHistory] @AllocationsDutyID,@DutyHistoryType,@UserID,@DutyHistory2,1,'DH'

				 IF ( ISNULL (@SchedulingPersonID,0) > 0 )
				   BEGIN
				    EXEC usp_mod_AllocationHistory @AllocationsSPID,@PersonHistoryType,@UserID,@DutyHistory2,1,'PH'
					EXEC @ReturnValue = usp_UpdateWTD @AllocationsSPID, @NetLogin	
				   END
						 
			    END

			END	

		 	UPDATE AllocationsJobs 
				set AJ_AllocationsDutyID = @AllocationsDutyID,
					AJ_UpdatedBy = @UserID,
					AJ_UpdatedDate = GETUTCDATE()
			WHERE AJ_AllocateJobID = @AllocateJobID

			SET @JobHistory = @JobHistory
							+' This Job ( '+ @JobName + ' ) was '
							+ CASE WHEN @DutyName IS NULL THEN 'Moved to unallocated job ' 
							       ELSE 'Moved to '+@DutyName END
							+ ' by '+ @Name + ' On '
						+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + '<hr>';
						
			EXEC [usp_mod_AllocationHistory] @AllocateJobID,@JobHistoryType,@UserId,@DutyHistory2,1

			IF(@role=1)	
			  EXEC usp_mod_PublishIndividulAllocations @AllocationsID, @AllocationsDutyID, @AllocationsSPID;

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
		ROLLBACK TRANSACTION
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
			@UserID	

	 	 SELECT @@IDENTITY as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage  

	END CATCH;

 END