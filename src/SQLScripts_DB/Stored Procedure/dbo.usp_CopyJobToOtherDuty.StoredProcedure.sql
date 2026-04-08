USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_CopyJobToOtherDuty]    Script Date: 2/18/2026 9:52:25 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER               PROCEDURE [dbo].[usp_CopyJobToOtherDuty]
@SchedulingPersonID INT NULL,
@DutyStartDate		DATE,
@DutyEndDate		DATE,
@TeamID				INT,
@JobID				INT,
@UserID				INT,
@role				INT = 0

AS
BEGIN

 SET NOCOUNT ON;
 
 DECLARE @AllocationsDutyID int, 
         @AllocateJobID INT,
		 @AllocationsID	INT,
		 @JobName NVARCHAR(100),
		 @History1 varchar(Max) ='',
		 @History2 varchar(Max) ='',
		 @DutyStartTimeSec INT,
		 @DutyEndTimeSec INT,
		 @ReturnValue INT=0,
		 @NetLogin NVARCHAR(25),
		 @status	 INT ,
		 @WTDFlag INT=0,
		 @DisplayName NVARCHAR(100),
		 @Name NVARCHAR(100),
		 @UpdateAllocationFlag BIT = 1,
		 @FromSchedulingTeamName VARCHAR(120),
		 @JobStartTimeLocal		DATETIME,
		 @JobEndTimeLocal		DATETIME,
		 @JobStartTimeSec		INT,
		 @JobEndTimeSec			INT,
		 @MaxJobID				INT,
		 @DutyName					NVARCHAR(100),
		 @DutyType					INT,
		 @DutyDate					DATE,
		 @DutyStartTimeLocal		DATETIME,
		 @DutyEndTimeLocal			DATETIME,
		 @NewDutyStartTimeLocal	DATETIME,
		 @NewDutyEndTimeLocal		DATETIME,
		 @DutyHistoryType			INT,
		 @PersonHistoryType		INT,
		 @JobHistoryType			INT,
		 @AllocationsSPID			INT,
		 @IsDutyOverLap			BIT,
		 @prevOverlapMsg			VARCHAR(500),
		 @nextOverlapMsg			VARCHAR(500),
		 @OvreLapMsg				VARCHAR(1000),
		 @JobStartTimeOverNightFlag		BIT = 0,
		 @JobEndTimeOverNightFlag		BIT = 0;

 DECLARE @TempAllocation	TABLE (AllocationID INT );
 DECLARE @TempJob	TABLE (AllocateJobID INT);

	SELECT @NetLogin = UD_NetLogin,
		   @Name = UD_DisplayName
	  FROM UserDetails
	 WHERE UD_UserID = @UserID

   select @JobHistoryType = ID 
	 from historytypes 
	WHERE historytype='AllocationJobs'

   select @DutyHistoryType = ID 
	 from historytypes 
	WHERE historytype='AllocationDuty'

   select @PersonHistoryType = ID 
	 from historytypes 
	WHERE historytype='AllocationScheduledPerson'

  select @JobName=AJ_JobName,
		 @FromSchedulingTeamName = ST.schedulingTeamName,
         @JobStartTimeSec=AJ_JobStartTimeSec,
		 @JobEndTimeSec=AJ_JobEndTimeSec,
		 @JobStartTimeLocal = AJ_JobStartTimeLocal,
		 @JobEndTimeLocal	= AJ_JobEndTimeLocal,
		 @JobStartTimeOverNightFlag  =  CASE WHEN DATEDIFF(DAY,AD_DutyDate,AJ_JobStartTimeLocal) > 0 THEN 1 ELSE 0 END,
		 @JobEndTimeOverNightFlag = CASE WHEN DATEDIFF(DAY,AD_DutyDate,AJ_JobEndTimeLocal) > 0 THEN 1 ELSE 0 END
    FROM AllocationsJobs (NOLOCK) AJ
   INNER JOIN AllocationsDuties AD on AJ_AllocationsDutyID = AD_AllocationsDutyID
   INNER JOIN Allocations (NOLOCK) AL ON AL_AllocationsID = AD_AllocationsID
   INNER JOIN schedulingTeams (NOLOCK) ST ON AL_SchedulingTeamID = ST.schedulingTeamId
   where AJ_AllocateJobID = @jobid;

	IF ( ISNULL(@SchedulingPersonID,0) > 0 )
	 BEGIN
			IF EXISTS ( SELECT 1
						  FROM Allocations AL
						 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
						 INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
														AND TD.ixDayInWeek = AD_iDay
						 INNER JOIN AllocationsJobs AJ on AJ_AllocationsDutyID = AD_AllocationsDutyID
						 INNER JOIN AllocationsScheduledPersons ASP on ASP_AllocationsDutyID = AD_AllocationsDutyID
						 WHERE TD.dDateTime between @DutyStartDate and @DutyEndDate
						   AND ASP_SchedulingPersonID = @SchedulingPersonID
						   AND AL_SchedulingTeamID = @TeamID
						   AND AJ_JobStatus <> 9
						   AND AJ_JobStartTimeLocal < DATEADD(DAY, -1*DATEDIFF(DAY,td.dDateTime,@JobEndTimeLocal) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) 
						   AND AJ_JobEndTimeLocal > DATEADD(DAY, -1*DATEDIFF(DAY,td.dDateTime,@JobStartTimeLocal) + @JobStartTimeOverNightFlag,@JobStartTimeLocal))

		BEGIN
		 SELECT 1 as SPExecStatus,
				'The Job you are trying to add will conflict with existing job. Please Try Again.' AS SPMessage	
		 RETURN;
		END			  		  

     END

	BEGIN TRY
	 BEGIN TRANSACTION
	  IF ( ISNULL(@SchedulingPersonID,0) = 0 )
		 BEGIN	
	    
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
			OUTPUT INSERTED.AJ_AllocateJobID INTO @TempJob
			SELECT AD.AD_AllocationsDutyID,
				   AJ.AJ_MasterJobID,
				   AJ_ProgrammeID as programmeid,
				   AJ_Contact,
				   AJ_Location,
				   AJ_JobName,
				   AJ_JobStartTimeSec,
				   AJ_JobEndTimeSec,
				   DATEADD(DAY, DATEDIFF(DAY,td.dDateTime,AJ_JobStartTimeUTC),AJ_JobStartTimeUTC),
				   DATEADD(DAY, DATEDIFF(DAY,td.dDateTime,AJ_JobEndTimeUTC),AJ_JobEndTimeUTC),	
				   DATEADD(DAY, DATEDIFF(DAY,td.dDateTime,AJ_JobStartTimeLocal),AJ_JobStartTimeLocal),
				   DATEADD(DAY, DATEDIFF(DAY,td.dDateTime,AJ_JobEndTimeLocal),AJ_JobEndTimeLocal),						 
				   AJ_JobBGColour as jobbackcolour,
				   AJ_JobFontColour as jobfontcolour,
				   AJ_Comments,
				   AJ_JobStatus,				   
				   AJ_JobInfo,
				   @UserID,
				   getdate()
			 FROM AllocationsJobs AJ
			INNER JOIN Allocations AL on 1 = 1
			INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
			INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
														AND TD.ixDayInWeek = AD_iDay
			 WHERE TD.dDateTime between @DutyStartDate and @DutyEndDate
			   AND AD_DutyType = 10
			   AND AL_SchedulingTeamID = @TeamID
			   AND AJ_AllocateJobID = @JobID
			  
                SET @History2 ='This Job ( '+ @jobname + ' ) was Copied from '
				              +@FromSchedulingTeamName+' to Unallocated ' + ' by '+ @Name + ' On '
							  + FORMAT(Getdate(),'dd/MM/yyyy') + ' at ' + FORMAT(Getdate(),'HH:mm')+ '<hr>';				

				 INSERT INTO history
				  (
						historytype,
						attributeid,
						datetime,
						userid,
						history
				  )
				SELECT ht.id AS historytype,
					   TJ.AllocateJobID AS attributeid,
					   getdate(),
					   @UserID,
					   @History2
				  FROM @TempJob TJ
				 INNER JOIN historytypes ht on 1=1
				 WHERE historytype='AllocationJobs'

		 END
        ELSE
		 BEGIN
		 
		   DECLARE CUR_AL CURSOR FOR
		     SELECT AD_DutyName,
					AD_DutyType,
					AD_DutyDate,
					AD_StartTimeSec,
					AD_EndTimeSec,
					AD_DutyStartTimeLocal,
					AD_DutyEndTimeLocal,
					UD_UserID,
					UD_DisplayName,
				    ASP_AllocationsSPID,
					AD_AllocationsDutyID,
					AL_AllocationsID
			  FROM Allocations AL
			  INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
			  INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
										AND TD.ixDayInWeek = AD_iDay
			  INNER JOIN AllocationsScheduledPersons ASP on ASP_AllocationsDutyID = AD_AllocationsDutyID
														--AND AL_AllocationsID = ASP_AllocationsID
			  INNER JOIN UserDetails ON UD_UserID = ASP_SchedulingPersonID
			  WHERE TD.dDateTime between @DutyStartDate and @DutyEndDate
			    AND ASP_SchedulingPersonID = @SchedulingPersonID
			    AND AL_SchedulingTeamID = @TeamID
				AND AD_DutyType < 7

           OPEN CUR_AL

           FETCH NEXT FROM CUR_AL INTO  @DutyName,
										@DutyType,
										@DutyDate,
										@DutyStartTimeSec,
										@DutyEndTimeSec,
										@DutyStartTimeLocal,
										@DutyEndTimeLocal,
										@SchedulingPersonID,
										@DisplayName,
										@AllocationsSPID,
										@AllocationsDutyID,
										@AllocationsID

		   WHILE @@FETCH_STATUS = 0
			BEGIN				 
					  
			 IF ( @DutyStartTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal) ) + @JobStartTimeOverNightFlag,@JobStartTimeLocal) 
				OR @DutyEndTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
			   BEGIN
				IF (@AllocationsSPID > 0 )
				  BEGIN
				   IF ( @DutyStartTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal) 
						AND @DutyEndTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
				     BEGIN					
				   			SELECT @IsDutyOverLap = IsDutyOverLap,
								   @prevOverlapMsg = PrevDayOvrlapMsg,
								   @nextOverlapMsg = NextDayOvrlapMsg
							  FROM ufn_check_DutyOverLap(@AllocationsSPID, 
												DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal),
											    DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )

							SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

							IF ( @IsDutyOverLap = 1 )
							 BEGIN
							  THROW 51001, @OvreLapMsg, 1;
							 END;

				     END
				   IF ( @DutyStartTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal) 
						AND @DutyEndTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
				     BEGIN

				   			SELECT @IsDutyOverLap = IsDutyOverLap,
								   @prevOverlapMsg = PrevDayOvrlapMsg
							  FROM ufn_check_DutyOverLap_PrevDay(@AllocationsSPID, 
							  DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag ,@JobStartTimeLocal) )							

							IF ( @IsDutyOverLap = 1 )
							 BEGIN
							  THROW 51002, @prevOverlapMsg, 1;
							 END;							

				     END
				   IF ( @DutyStartTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal) 
						AND @DutyEndTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
				     BEGIN

				   			SELECT @IsDutyOverLap = IsDutyOverLap,
								   @nextOverlapMsg = NextDayOvrlapMsg
							  FROM ufn_check_DutyOverLap_NextDay(@AllocationsSPID, 
											DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)+@JobEndTimeOverNightFlag),@JobEndTimeLocal)	)						

							IF ( @IsDutyOverLap = 1 )
							 BEGIN
							  THROW 51003, @nextOverlapMsg, 1;
							 END;
				     END
				   END

				   		SET @History1 ='Duty start time update because of Move job start time by ' 
									  + @Name
									  +' From '+right('0'+CAST( isnull(@DutyStartTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@DutyStartTimeSec,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(@JobStartTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@JobStartTimeSec,0) % 3600)/60 AS varchar(2)),2)								  
									  +' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				   		SET @History2 = ' Duty end time update because to Move job end time by ' + @Name 
									  + ' From '+right('0'+CAST( isnull(@DutyEndTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@DutyEndTimeSec,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(@JobEndTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@JobEndTimeSec,0) % 3600)/60 AS varchar(2)),2)										  
									  + ' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				 IF ( @DutyStartTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal)  
						AND @DutyEndTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
				  BEGIN
				    
					SET @DutyStartTimeSec	= @JobStartTimeSec
					SET @DutyEndTimeSec		= @JobEndTimeSec
					SET @DutyStartTimeLocal	= DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal)
					SET @DutyEndTimeLocal	= DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)+@JobEndTimeOverNightFlag),@JobEndTimeLocal)
				    SET @History2 = @History1 + CHAR(13) + @History2

				  END
				    
				 IF ( @DutyStartTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal)  
					  AND @DutyEndTimeLocal > DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
				  BEGIN

					SET @DutyStartTimeSec	= @JobStartTimeSec
					SET @DutyStartTimeLocal	= DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal)
					SET @History2 = @History1 

				  END
				 
				 IF ( @DutyStartTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobStartTimeLocal)) + @JobStartTimeOverNightFlag,@JobStartTimeLocal)  
					AND @DutyEndTimeLocal < DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal) )
				  BEGIN

					SET @DutyEndTimeSec		= @JobEndTimeSec
					SET @DutyEndTimeLocal	= DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,@JobEndTimeLocal)) + @JobEndTimeOverNightFlag,@JobEndTimeLocal)

				  END
 
				 EXEC @ReturnValue = usp_UpdateDuty @AllocationsDutyID,@DutyStartTimeSec, 
													@DutyEndTimeSec, @DutyStartTimeLocal, 
													@DutyEndTimeLocal,@UserID,@Role

					IF ( @ReturnValue > 0 )
					 BEGIN
					   THROW 51004, 'Error while updating duty details', 1;
					 END;

				 EXEC [usp_mod_AllocationHistory] @AllocationsDutyID,@DutyHistoryType,@UserID,@History2,1,'DH'

				 IF ( ISNULL (@SchedulingPersonID,0) > 0 )
				   BEGIN
				    EXEC usp_mod_AllocationHistory @AllocationsSPID,@PersonHistoryType,@UserID,@History2,1,'PH'
					EXEC @ReturnValue = usp_UpdateWTD @AllocationsSPID, @NetLogin	
				   END
						 
			    END

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
			SELECT @AllocationsDutyID,
				   AJ.AJ_MasterJobID,
				   AJ_ProgrammeID as programmeid,
				   AJ_Contact,
				   AJ_Location,
				   AJ_JobName,
				   AJ_JobStartTimeSec,
				   AJ_JobEndTimeSec,
				   DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,AJ_JobStartTimeUTC)) + @JobStartTimeOverNightFlag, AJ_JobStartTimeUTC),
				   DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,AJ_JobEndTimeUTC)) + @JobEndTimeOverNightFlag, AJ_JobEndTimeUTC),	
				   DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,AJ_JobStartTimeLocal)) + @JobStartTimeOverNightFlag, AJ_JobStartTimeLocal),
				   DATEADD(DAY, -1*(DATEDIFF(DAY,@DutyDate,AJ_JobEndTimeLocal)) + @JobEndTimeOverNightFlag,AJ_JobEndTimeLocal),						 
				   AJ_JobBGColour as jobbackcolour,
				   AJ_JobFontColour as jobfontcolour,
				   AJ_Comments,
				   AJ_JobStatus,				   
				   AJ_JobInfo,
				   @UserID,
				   getdate()
			 FROM AllocationsJobs AJ			
			 WHERE AJ_AllocateJobID = @JobID

			 SET @AllocateJobID = @@IDENTITY

			SET @History2 = 'This Job ( '+ @jobname + ' ) was Copied from '
				              +@FromSchedulingTeamName
				              +' to ' + @DutyName + ' by '+ @Name + ' On '
							  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+ '<hr>';
						
			EXEC [usp_mod_AllocationHistory] @AllocateJobID,@JobHistoryType,@UserId,@History2,1

			IF(@role=1)	
			  EXEC usp_mod_PublishIndividulAllocations @AllocationsID, @AllocationsDutyID, @AllocationsSPID;

			    DECLARE @PublishStatus INT = 0

				IF(@role=1)	SET @PublishStatus = 1
				EXEC @ReturnValue = usp_CreateAllocationsUpdate  @AllocationsID,
														@AllocationsDutyID,
														@AllocationsSPID,
														@PublishStatus,
														@NetLogin
																 
				IF ( @ReturnValue > 0 )
				  BEGIN
					THROW 51000, 'Error while creating Duty update status', 1;
				  END 	


				   FETCH NEXT FROM CUR_AL INTO  @DutyName,
												@DutyType,
												@DutyDate,
												@DutyStartTimeSec,
												@DutyEndTimeSec,
												@DutyStartTimeLocal,
												@DutyEndTimeLocal,
												@SchedulingPersonID,
												@DisplayName,
												@AllocationsSPID,
												@AllocationsDutyID,
												@AllocationsID;

		  END	
		   
			   CLOSE CUR_AL;

			   DEALLOCATE CUR_AL;

		/*BEGIN		     			

		    DELETE @TempJob
  
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
		    OUTPUT INSERTED.AJ_AllocateJobID INTO @TempJob
			SELECT AD.AD_AllocationsDutyID,
				   AJ.AJ_MasterJobID,
				   AJ_ProgrammeID as programmeid,
				   AJ_Contact,
				   AJ_Location,
				   AJ_JobName,
				   AJ_JobStartTimeSec,
				   AJ_JobEndTimeSec,
				   DATEADD(DAY, -1*(DATEDIFF(DAY,td.dDateTime,AJ_JobStartTimeUTC)),AJ_JobStartTimeUTC),
				   DATEADD(DAY, -1*(DATEDIFF(DAY,td.dDateTime,AJ_JobEndTimeUTC)),AJ_JobEndTimeUTC),	
				   DATEADD(DAY, -1*(DATEDIFF(DAY,td.dDateTime,AJ_JobStartTimeLocal)),AJ_JobStartTimeLocal),
				   DATEADD(DAY, -1*(DATEDIFF(DAY,td.dDateTime,AJ_JobEndTimeLocal)),AJ_JobEndTimeLocal),						 
				   AJ_JobBGColour as jobbackcolour,
				   AJ_JobFontColour as jobfontcolour,
				   AJ_Comments,
				   AJ_JobStatus,				   
				   AJ_JobInfo,
				   @UserID,
				   getdate()
			 FROM AllocationsJobs AJ
			INNER JOIN Allocations AL on 1 = 1
			INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
			INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
														AND TD.ixDayInWeek = AD_iDay
			 WHERE TD.dDateTime between @DutyStartDate and @DutyEndDate
			   AND AD_DutyType = 10
			   AND AL_SchedulingTeamID = @TeamID
			   AND AJ_AllocateJobID = @JobID
			   AND TD.dDateTime IN ( SELECT dDateTime FROM
									  (SELECT TD.dDateTime,
										 	  AD_DutyDate
										FROM Allocations AL
									   INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek			 
										LEFT JOIN AllocationsScheduledPersons ASP on ASP_AllocationsID = AL_AllocationsID
																				AND TD.ixDayInWeek = ASP_iDay
																				AND ASP_SchedulingPersonID = @SchedulingPersonID
										LEFT JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
																	AND ASP_AllocationsDutyID = AD_AllocationsDutyID
																	AND AD_DutyType NOT IN (7,8,9)
										WHERE TD.dDateTime between @DutyStartDate and @DutyEndDate
										AND AL_SchedulingTeamID = @TeamID
									   ) FD WHERE AD_DutyDate IS NULL
								    )

                SET @History2 ='This Job ( '+ @jobname + ' ) was Copied from '
				              +@FromSchedulingTeamName+' to Unallocated ' + ' by '+ @Name + ' On '
							  + FORMAT(Getdate(),'dd/MM/yyyy') + ' at ' + FORMAT(Getdate(),'HH:mm')+ '<hr>';				

				 INSERT INTO history
				  (
						historytype,
						attributeid,
						datetime,
						userid,
						history
				  )
				SELECT ht.id AS historytype,
					   AllocateJobID AS attributeid,
					   getdate(),
					   @UserID,
					   @History2
				  FROM @TempJob
				 INNER JOIN historytypes ht on 1=1
				 WHERE historytype='AllocationJobs'
		END */
	END

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
			@UserID	

	 	 SELECT ISNULL(@@IDENTITY,ERROR_NUMBER()) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage  

	END CATCH;

 END