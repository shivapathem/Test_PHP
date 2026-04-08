USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_Allocation_Jobs]    Script Date: 20/03/2026 18:40:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                      PROCEDURE [dbo].[usp_mod_Allocation_Jobs]
@JobID				INT,
@AllocationsDutyID	INT,
@JobName			NVARCHAR(100),
@StartTime			INT,
@EndTime			INT,
@DutyDate			DATE,
@Info				VARCHAR(255),
@JobBackColour		VARCHAR(10),
@JobFontColour		VARCHAR(10),
@TeamId				INT,
@Contact			VARCHAR(60),
@Location			VARCHAR(25),
@ProgrammeId		INT,
@AfterMidnight		INT,
@SchedulingPersonID INT,
@Role				INT,
@UserID				INT

AS
BEGIN
	
	DECLARE		@DutyHistory			VARCHAR(Max)='',
				@JobHistory				VARCHAR(Max)='',
				@JobHistoryDuty 		VARCHAR(Max)='',
				@UpdateHistory			VARCHAR(MAX)='',
				@OldAllocationID		int,
				@OldAllocationsDutyID	int,
				@OldMasterDutyID		int,
				@OldWeekNumber			int,
				@OldiDay				int,
				@OldJobName				varchar(100),
				@OldStartTime			INT,
				@OldEndTime				INT,
				@OldJobBackColour		varchar(10),
				@OldJobFontColour		varchar(10),
				@OldProgrammeId			int,
				@Oldaftermidnight		int,
				@Oldunallocated			int,
				@Oldinfo				varchar(max),
				@Oldcontact				varchar(20),
				@Oldlocation			varchar(20),
				@dutyStartTime			INT,
				@dutyEndTime			INT,
				@pNetLogin				NVARCHAR(25),
				@AllocationsSPID		INT,
				@ReturnValue			INT=0,
				@DutyName				NVARCHAR(100),
				@DutyType				INT,
				@PrevEndTime			INT,
				@NextStartTime			INT,
				@DisplayName			NVARCHAR(100), 
				@voldlabel				NVARCHAR(50),
				@vnewlabel				NVARCHAR(50),
				@UpdateAllocationFlag	BIT = 0,
				@NewDutyStartTime		INT,
				@NewDutyEndTime			INT,
				@DutyStartTimeLocal		DATETIME,
				@DutyEndTimeLocal		DATETIME,
				@MasterDutyID			INT,
				@jobHistoryType			INT,
				@DutyHistoryType		INT,
				@PersonHistoryType		INT,
				@JobStartTimeSec		INT,
				@JobEndTimeSec			INT,
				@JobStartTimeLocal		DATETIME,
				@JobEndTimeLocal		DATETIME,
				@OldJobStartTimeLocal	DATETIME,
				@OldJobEndTimeLocal		DATETIME,
				@IsDutyOverLap			BIT,
				@prevOverlapMsg			VARCHAR(500),
				@nextOverlapMsg			VARCHAR(500),
				@OvreLapMsg				VARCHAR(1000),
				@vName					VARCHAR(50),
				@NewDutyStartTimeLocal  DATETIME,
				@NewDutyEndTimeLocal    DATETIME,
				@UpdateJobFlag			BIT = 0,
				@IsDutyEdited			BIT = 0;

	SET NOCOUNT ON;

		IF ( ISNULL(@TeamId,0) = 0 and ISNULL(@AllocationsDutyID,0) = 0  )
		  BEGIN
			SELECT 1 as SPExecStatus,
			       'Please choose Scheduling team.' as SPMessage 

			  IF ( @@TRANCOUNT  > 0 ) 
			   BEGIN
				ROLLBACK TRANSACTION
			   END
			
			RETURN;

		  END
	
	   SELECT @pNetLogin = UD_NetLogin,
			  @vName	 = UD_DisplayName
		 FROM UserDetails (NOLOCK) 
		WHERE UD_UserID = @UserID

	   SELECT @DisplayName = UD_DisplayName
		 FROM UserDetails (NOLOCK) 
		WHERE UD_UserID = @SchedulingPersonID

	   SELECT @DutyHistoryType = ID
		 FROM HistoryTypes
		WHERE HistoryType = 'AllocationDuty'

		SELECT @PersonHistoryType = ID
		 FROM HistoryTypes
		WHERE HistoryType = 'AllocationScheduledPerson'

		SELECT @JobHistoryType = ID
		 FROM HistoryTypes
		WHERE HistoryType = 'AllocationJobs'


	    SET @NewDutyStartTime =  case when @StartTime%3600/60 > 0 and  @StartTime%3600/60 < 15 THEN @StartTime - (@StartTime%3600) 
                                      when @StartTime%3600/60 > 15 and  @StartTime%3600/60 < 30 THEN @StartTime - (@StartTime%3600) + 900
									  when @StartTime%3600/60 > 30 and  @StartTime%3600/60 < 45 THEN @StartTime - (@StartTime%3600) + 1800
									  when @StartTime%3600/60 > 45  THEN @StartTime - (@StartTime%3600) + 2700
									  ELSE @StartTime
								  end

        SET @NewDutyStartTime = CASE WHEN @NewDutyStartTime >= 86400 then ( @NewDutyStartTime - 86400) else @NewDutyStartTime end 

	    SET @NewDutyEndTime =	case when @EndTime%3600/60 > 0 and  @EndTime%3600/60 < 15 THEN @EndTime - (@EndTime%3600) + 900
                                     when @EndTime%3600/60 > 15 and  @EndTime%3600/60 < 30 THEN @EndTime - (@EndTime%3600) + 1800
									 when @EndTime%3600/60 > 30 and  @EndTime%3600/60 < 45 THEN @EndTime - (@EndTime%3600) + 2700
									 when @EndTime%3600/60 > 45  THEN @EndTime - (@EndTime%3600) + 3600
									 ELSE @EndTime
								  end	

		SET @NewDutyEndTime = CASE WHEN @NewDutyEndTime >= 86400 then ( @NewDutyEndTime - 86400) else @NewDutyEndTime end 

		SET @JobStartTimeLocal = CASE WHEN @StartTime = 0 AND @AfterMidnight = 1 THEN DATEADD(DAY,1,@DutyDate)
									  WHEN @StartTime = 0 AND ISNULL(@AfterMidnight,0) = 0 THEN @DutyDate
										  WHEN @StartTime = 86400 THEN DATEADD(DAY,1,@DutyDate)
										  WHEN @StartTime > 86400 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@StartTime - 86400)
										  WHEN @AfterMidnight = 1 AND @StartTime < @EndTime 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate), @StartTime)
										  ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@StartTime ) 
										END

		SET @JobEndTimeLocal = CASE WHEN @StartTime > 0 AND @EndTime = 0 
										  THEN DATEADD(DAY,1,@DutyDate)
										  WHEN @EndTime > 86400 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@EndTime-86400)
										  WHEN @EndTime = 86400 
										  THEN DATEADD(DAY,1,@DutyDate)
										  WHEN @AfterMidnight = 1 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@EndTime)
										  ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@EndTime) END

		SET @NewDutyStartTimeLocal = CASE WHEN @StartTime = 0 AND @AfterMidnight = 1 THEN DATEADD(DAY,1,@DutyDate)
									  WHEN @StartTime = 0 AND ISNULL(@AfterMidnight,0) = 0 THEN @DutyDate
										  WHEN @NewDutyStartTime = 86400 THEN DATEADD(DAY,1,@DutyDate)
										  WHEN @NewDutyStartTime > 86400 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@NewDutyStartTime - 86400)
										  WHEN @AfterMidnight = 1 AND @NewDutyStartTime < @NewDutyEndTime
										  THEN DATEADD(DAY,1,@DutyDate)
										  ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@NewDutyStartTime ) 
										END
		SET @NewDutyEndTimeLocal = CASE WHEN @NewDutyStartTime > 0 AND @NewDutyEndTime = 0 
										  THEN DATEADD(DAY,1,@DutyDate)
										  WHEN @NewDutyEndTime > 86400 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@NewDutyEndTime - 86400)
										  WHEN @NewDutyEndTime = 86400 
										  THEN DATEADD(DAY,1,@DutyDate)
										  WHEN @AfterMidnight = 1 
										  THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@NewDutyEndTime)
										  ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@NewDutyEndTime) END
  
  BEGIN TRY
    BEGIN TRANSACTION

	 IF ( ISNULL(@AllocationsDutyID,0) > 0 )
	  BEGIN
		IF EXISTS ( SELECT 1
						FROM AllocationsJobs
						WHERE AJ_AllocationsDutyID = @AllocationsDutyID
						  AND AJ_JobStatus <> 9
						  AND AJ_JobStartTimeLocal < @JobEndTimeLocal 
						  AND AJ_JobEndTimeLocal > @JobStartTimeLocal
						  AND AJ_AllocateJobID <> @JobID)

		BEGIN
		 SELECT 1 AS SPExecStatus,
				'The Job you are trying to add will conflict with existing job. Please Try Again.' AS SPMessage				

			ROLLBACK TRANSACTION

		 RETURN;
		
		END
	  END

	  IF ( ISNULL(@JobID,0) > 0 )
	   BEGIN
			 SELECT @OldAllocationID =AL.AL_AllocationsID,
					@OldAllocationsDutyID=AD_AllocationsDutyID ,
					@OldMasterDutyID=AD_MasterDutyID,
					@OldWeekNumber =AL.AL_WeekNumber,
					@OldiDay =AD.AD_iDay,
					@OldJobName =AJ.AJ_JobName,
					@OldStartTime=AJ.AJ_JobStartTimeSec,
					@OldEndTime =AJ.AJ_JobEndTimeSec,
					@OldJobStartTimeLocal = AJ.AJ_JobStartTimeLocal,
					@OldJobEndTimeLocal = AJ.AJ_JobEndTimeLocal,
					@OldJobBackColour =AJ.AJ_JobBGColour,
					@OldJobFontColour =AJ.AJ_JobFontColour,
					@OldProgrammeId = AJ.AJ_ProgrammeID,
					@Oldinfo = AJ.AJ_JobInfo,
					@Oldcontact =AJ.AJ_Contact,
					@Oldlocation =AJ.AJ_Location,
					@DutyType = AD_DutyType
			   FROM Allocations AL(NOLOCK) 
			  INNER JOIN AllocationsDuties AD (NOLOCK) ON AD.AD_AllocationsID=AL.AL_AllocationsID
			  INNER JOIN AllocationsJobs AJ(NOLOCK) ON AJ.AJ_AllocationsDutyID=AD.AD_AllocationsDutyID
			  WHERE AJ.AJ_AllocateJobID = @JobID ;
		END;

		IF ( ISNULL(@AllocationsDutyID,0) = 0 AND ISNULL(@OldAllocationID,0) = 0)
		  BEGIN
		  
			  SELECT @AllocationsDutyID = AD_AllocationsDutyID 
			    FROM Allocations (NOLOCK) AL 
			   INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
			   INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
											  AND TD.ixDayInWeek = AD_iDay
			   WHERE TD.dDateTime = @DutyDate 
			     AND AL_SchedulingTeamID = @TeamId 
				 AND AD_DutyType = 10	
				 
				SET @DutyType = 10

                SET @JobHistory ='This Job ( '+ ISNULL(@JobName,'') + ' ) was Moved to Unallocated ' 
								+ ' by '+ ISNULL(@vName,'') + ' On '
							  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+ '<hr>';			

		  END
		ELSE
		 IF ( @OldAllocationsDutyID <> @AllocationsDutyID 
				OR @OldJobStartTimeLocal <> @JobStartTimeLocal 
				OR @OldJobEndTimeLocal <>  @JobEndTimeLocal 
				OR  ( ISNULL(@JobID,0) = 0  AND ISNULL(@AllocationsDutyID,0) > 0 ) )
		  BEGIN		  
		    
			 SELECT @DutyName			= AD_DutyName,
					@DutyType			= AD_DutyType,
					@dutyStartTime		= AD_StartTimeSec,
					@dutyEndTime		= AD_EndTimeSec,
					@DutyStartTimeLocal	= AD_DutyStartTimeLocal,
					@DutyEndTimeLocal	= AD_DutyEndTimeLocal,
					@SchedulingPersonID	= UD_UserID,
					@DisplayName		= UD_DisplayName,
					@AllocationsSPID	= ASP_AllocationsSPID,
					@IsDutyEdited		= AD_IsDutyEdited
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

						SET  @JobStartTimeSec = ISNULL(@NewDutyStartTime,@OldStartTime)
						SET  @JobEndTimeSec	  = ISNULL(@NewDutyEndTime,@OldEndTime)

				   		SET @UpdateHistory ='Duty start time update because of assign job start time by ' 
									  + @vName
									  +' From '+right('0'+CAST( isnull(@dutyStartTime,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@dutyStartTime,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(@JobStartTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@JobStartTimeSec,0) % 3600)/60 AS varchar(2)),2)								  
									  +' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				   		SET @DutyHistory = ' Duty end time update because of assign job end time by ' + @vName 
									  + ' From '+right('0'+CAST( isnull(@dutyEndTime,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@dutyEndTime,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(@JobEndTimeSec,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@JobEndTimeSec,0) % 3600)/60 AS varchar(2)),2)										  
									  + ' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				 IF ( @DutyStartTimeLocal > @JobStartTimeLocal AND @DutyEndTimeLocal < @JobEndTimeLocal )
				  BEGIN
				    
					SET @dutyStartTime		= @NewDutyStartTime
					SET @dutyEndTime		= @NewDutyEndTime
					SET @DutyStartTimeLocal	= @NewDutyStartTimeLocal
					SET @DutyEndTimeLocal	= @NewDutyEndTimeLocal
				    SET @DutyHistory = @UpdateHistory + CHAR(13) + @DutyHistory

					SET @UpdateAllocationFlag = 1

				  END
				    
				 IF ( @DutyStartTimeLocal > @JobStartTimeLocal AND @DutyEndTimeLocal >= @JobEndTimeLocal )
				  BEGIN

					SET @dutyStartTime		= @NewDutyStartTime
					SET @DutyStartTimeLocal	= @NewDutyStartTimeLocal
					SET @DutyHistory = @UpdateHistory 

					SET @UpdateAllocationFlag = 1

				  END
				 
				 IF ( @DutyStartTimeLocal <= @JobStartTimeLocal AND @DutyEndTimeLocal < @JobEndTimeLocal )
				  BEGIN

					SET @dutyEndTime		= @NewDutyEndTime
					SET @DutyEndTimeLocal	= @NewDutyEndTimeLocal

					SET @UpdateAllocationFlag = 1

				  END
		   END	
		 END

		 IF ( ISNULL( @JobID ,0 ) > 0 )
		  BEGIN
			IF(@OldJobName COLLATE SQL_Latin1_General_CP1_CS_AS != @JobName COLLATE SQL_Latin1_General_CP1_CS_AS)
			 BEGIN
			  SET @UpdateHistory = 'Name  Changed From '
			                     +@OldJobName 
								 +' To '+@JobName+ ' ';
			  SET @UpdateJobFlag = 1
			 END

			IF(@OldStartTime != @StartTime)
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', StartTime Changed From '
									  + right('0'+CAST( isnull(case when @OldStartTime = 86400 
										then 0 else @OldStartTime end,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(case when @OldStartTime = 86400 
										then 0 else @OldStartTime end,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(case when @StartTime = 86400 
										then 0 else @StartTime end,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(case when @StartTime = 86400 
										then 0 else @StartTime end,0) % 3600)/60 AS varchar(2)),2)+ ' '							  
			  SET @UpdateJobFlag = 1
			 END

			IF(@OldEndTime != @EndTime)
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', EndTime Changed From '
									  + right('0'+CAST( isnull(case when @OldEndTime = 86400 
										then 0 else @OldEndTime end,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(case when @OldEndTime = 86400 
										then 0 else @OldEndTime end,0) % 3600)/60 AS varchar(2)),2)+' To '
									  + right('0'+CAST( isnull(case when @EndTime = 86400 
										then 0 else @EndTime end,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(case when @EndTime = 86400 
										then 0 else @EndTime end,0) % 3600)/60 AS varchar(2)),2)+ ' '							  
			  SET @UpdateJobFlag = 1
			 END

			IF( ISNULL(@OldProgrammeId,0) != @ProgrammeId)
			 BEGIN

			  SELECT @voldlabel = Programme FROM Programmes (Nolock) WHERE ID = @OldProgrammeId
			  SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @ProgrammeId

			  SET @UpdateHistory = @UpdateHistory + ', Programme Changed From '
			                     + ISNULL(@voldlabel,'')+' To '
								 + ISNULL(@vnewlabel,'')+ ' ';
			  SET @UpdateJobFlag = 1
			  END
	
			IF( ISNULL(@Oldinfo,'') != ISNULL(@info,''))
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', Info Changed  From '
			                     +ISNULL(@Oldinfo,'') +' To '+ISNULL(@info,'')+ ' ';
			  SET @UpdateJobFlag = 1
			 END

			IF(@contact!='' AND @Oldcontact != @contact)
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', Contact Changed From '
			                    +cast(ISNULL(@Oldcontact,'') as nvarchar) 
								+' To '+cast(ISNULL(@contact,'') as nvarchar)+ ' ';
			  SET @UpdateJobFlag = 1
			 END

			IF(@location!='' AND @Oldlocation != @location)
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', Location Changed  From '
			                     +cast(ISNULL(@Oldlocation,'') as nvarchar) +' To '
								 +cast(ISNULL(@location,'') as nvarchar)+ ' ';
			  SET @UpdateJobFlag = 1
			 END

			IF(@JobBackColour!='' AND @OldJobBackColour != @JobBackColour)
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', BackColor Changed  From <span style=''background-color:'
			                     +cast(ISNULL(@OldJobBackColour,'') as nvarchar) 
								 +'''>&nbsp;&nbsp;&nbsp;&nbsp;</span> To <span style=''background-color:'
								 +cast(ISNULL(@JobBackColour,'') as nvarchar)+ '''>&nbsp;&nbsp;&nbsp;&nbsp;</span> ';
			  SET @UpdateJobFlag = 1
			 END
		  
			IF(@JobFontColour!='' AND @OldJobFontColour != @JobFontColour)
			 BEGIN
			  SET @UpdateHistory = @UpdateHistory + ', FontColor Changed  From <span style=''background-color:'
			                     +cast(ISNULL(@OldJobFontColour,'') as nvarchar) 
								 +'''>&nbsp;&nbsp;&nbsp;&nbsp;</span> To <span style=''background-color:'
								 +cast(ISNULL(@JobFontColour,'') as nvarchar)+ '''>&nbsp;&nbsp;&nbsp;&nbsp;</span> ';
			  SET @UpdateJobFlag = 1
			 END

			IF ( ISNULL(@UpdateJobFlag,0) = 0 
				 AND  ( @OldJobStartTimeLocal <> @JobStartTimeLocal
						OR @OldJobEndTimeLocal <> @JobEndTimeLocal ) 
				)
			  BEGIN

			    SET @UpdateHistory = @UpdateHistory + ' StartTime and endtime updated to at the same time on the next day'
				SET @UpdateJobFlag = 1

			  END 

		  IF ( ISNULL(@AllocationsDutyID,0) > 0 AND ISNULL(@OldAllocationsDutyID,0) >  0
		     AND @AllocationsDutyID <> @OldAllocationsDutyID )
		   BEGIN
		    
			    SET @UpdateJobFlag = 1

                SET @JobHistory ='This Job ( '+ ISNULL(@JobName,'') + ' ) was Moved to duty '+@DutyName
								+ ' by '+ ISNULL(@vName,'') + ' On '
							  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+ '<hr>';			

		   END
 		 				
		  IF ( @UpdateJobFlag = 1 )
		   BEGIN
			UPDATE AllocationsJobs
			   SET AJ_AllocationsDutyID = CASE WHEN ISNULL(@AllocationsDutyID,0) = 0 
											   THEN @OldAllocationsDutyID
											   ELSE @AllocationsDutyID
											   END,
				   AJ_MasterJobID = @MasterDutyID,
				   AJ_ProgrammeID = @ProgrammeId,
				   AJ_Contact = @Contact,
				   AJ_Location = @Location,
				   AJ_JobName = @JobName,
				   AJ_JobStartTimeSec = @StartTime,
				   AJ_JobEndTimeSec = @EndTime,
				   AJ_JobStartTimeUTC = @JobStartTimeLocal,
				   AJ_JobEndTimeUTC = @JobEndTimeLocal,
				   AJ_JobStartTimeLocal = @JobStartTimeLocal,
				   AJ_JobEndTimeLocal = @JobEndTimeLocal,
				   AJ_JobBGColour = @JobBackColour,
				   AJ_JobFontColour = @JobFontColour,
				   AJ_JobStatus = 1,
				   AJ_JobInfo = @Info,
				   AJ_IsEditedJobAttention = CASE WHEN @role = 1 THEN 1 ELSE AJ_IsEditedJobAttention END,
				   AJ_UpdatedBy = @UserID,
				   AJ_UpdatedDate = getutcdate()	
			 WHERE AJ_AllocateJobID = @JobID;

			 SET @AllocationsDutyID = CASE WHEN ISNULL(@AllocationsDutyID,0) = 0 
											   THEN @OldAllocationsDutyID
											   ELSE @AllocationsDutyID
											   END

				SET @JobHistory = @JobHistory + 'Job '+ISNULL(@JobName,0) +' edited by '+ISNULL(@vName,0)+' on '
				+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +' '+@UpdateHistory+ '<hr>'; 
		   END

	     END
		ELSE
		 BEGIN
			
			INSERT INTO AllocationsJobs
						(AJ_AllocationsDutyID,
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
						 AJ_IsEditedJobAttention,
						 AJ_CreatedBy,
						 AJ_CreatedDate)
			   VALUES  ( @AllocationsDutyID,
						 @MasterDutyID,
						 @ProgrammeId,
						 @contact,
						 @location,
						 @JobName,
						 @StartTime,
						 @EndTime,
						 @JobStartTimeLocal,
						 @JobEndTimeLocal,
						 @JobStartTimeLocal,
						 @JobEndTimeLocal,
						 @JobBackColour,
						 @JobFontColour,
						 NULL,
						 1,
						 @info,
						 @role,
						 @UserID,
						 Getutcdate() ) 

				SET @JobID = @@IDENTITY

				SET @JobHistory = 'New job created by '+ ISNULL(@vName,'') + ' On '
								+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+ '<hr>';
			  
			    SET @UpdateJobFlag = 1	
				
				IF ( ISNULL(@DutyType,0) <> 10  )
				 BEGIN
				  
				  SET @JobHistoryDuty = REPLACE(@JobHistory,'New job created','New job created in duty')
				  EXEC [usp_mod_AllocationHistory] @AllocationsDutyID ,@DutyHistoryType,@UserID,@JobHistoryDuty,1,'DH'
				  IF ( @AllocationsSPID > 0 )
				  EXEC [usp_mod_AllocationHistory] @AllocationsSPID ,@PersonHistoryType,@UserID,@JobHistoryDuty,1,'PH'

				 END


			END

			IF ( @UpdateAllocationFlag = 1 )
			  BEGIN
			 
				 EXEC @ReturnValue = usp_UpdateDuty @AllocationsDutyID,@dutyStartTime, 
													@dutyEndTime, @DutyStartTimeLocal, 
													@DutyEndTimeLocal,@UserID,@Role

					IF ( @ReturnValue > 0 )
					 BEGIN
					   THROW 51004, 'Error while updating duty details', 1;
					 END;

				 EXEC [usp_mod_AllocationHistory] @AllocationsDutyID ,@DutyHistoryType,@UserID,@DutyHistory,1,'DH'				    

				 IF ( ISNULL (@SchedulingPersonID,0) > 0 )
				   BEGIN
				   
				     EXEC usp_mod_AllocationHistory @AllocationsSPID,@PersonHistoryType,@UserID,@DutyHistory,1,'PH'
					 
					 EXEC @ReturnValue = usp_UpdateWTD @AllocationsSPID, @pNetLogin	
				   
				   END
			  END

		  IF ( @UpdateAllocationFlag = 0 AND @UpdateJobFlag = 0  
				AND ISNULL(@AllocationsDutyID,0) > 0 
				AND ISNULL(@OldAllocationsDutyID,0) >  0
		        AND @AllocationsDutyID <> @OldAllocationsDutyID )
		   BEGIN
		  
				SET @UpdateJobFlag = 1

				UPDATE AllocationsJobs
				   SET AJ_AllocationsDutyID = CASE WHEN ISNULL(@AllocationsDutyID,0) = 0 
												   THEN @OldAllocationsDutyID
												   ELSE @AllocationsDutyID
												   END,
					   AJ_IsEditedJobAttention = CASE WHEN @role = 1 THEN 1 ELSE AJ_IsEditedJobAttention END,
					   AJ_UpdatedBy = @UserID,
					   AJ_UpdatedDate = getutcdate()				   
				 WHERE AJ_AllocateJobID = @JobID;

                SET @JobHistory ='This Job ( '+ ISNULL(@JobName,'') + ' ) was Moved to duty '+@DutyName
								+ ' by '+ ISNULL(@vName,'') + ' On '
							  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+ '<hr>';			

		   END


			IF ( @UpdateAllocationFlag = 0 AND @UpdateJobFlag = 1 AND ISNULL(@IsDutyEdited,0) = 0 AND @DutyType < 7 )
			  UPDATE AllocationsDuties
			     SET AD_IsDutyEdited = 1
			   WHERE AD_AllocationsDutyID = @AllocationsDutyID

			IF (  @UpdateJobFlag = 1 )
			 EXEC usp_mod_AllocationHistory @JobID,@jobHistoryType,@UserID,@JobHistory,1	  
						 
			IF(@role=1)
				EXEC usp_mod_PublishIndividulAllocations 0,@AllocationsDutyID,@AllocationsSPID

				EXEC @ReturnValue = usp_CreateAllocationsUpdate  0,
														@AllocationsDutyID,
														@AllocationsSPID,
														@role,
														@pNetLogin
																 
				IF ( @ReturnValue > 0 )
				  BEGIN
					THROW 51000, 'Error while creating Duty update status', 1;
				  END 	
			 

	    IF ( @@TRANCOUNT  > 0 ) 
           COMMIT  TRANSACTION 
	   
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