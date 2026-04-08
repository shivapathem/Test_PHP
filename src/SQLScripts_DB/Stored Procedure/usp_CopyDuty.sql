USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_CopyDuty]    Script Date: 30/03/2026 14:09:12 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                       PROCEDURE [dbo].[usp_CopyDuty]
@CopyDutyID         INT,
@FromDate           DATE,
@ToDate             DATE,
@TeamID             INT,
@SchduledPersonID   INT,
@pNetLogin          VARCHAR(30)
AS
BEGIN

   SET NOCOUNT ON;

   SET DATEFORMAT YMD;

   DECLARE   @vname                VARCHAR(50),
			 @vuserID              INT,
			 @PrevEndTime          INT,
			 @NextStartTime        INT,
			 @Message              VARCHAR(500),
			 @IsFreelancer         BIT = 0,
			 @return_value         INT,
			 @AllocationsSPID	   INT,
			 @IshomeTeam		   INT,
			 @CopyFlag			   BIT = 0,
			 @ReturnValue		   INT,
			 @MidnightFlag		   INT = 0;

   DECLARE	 @FromIDay             INT,
			 @FromIsHomeTeam       INT,
			 @FromMarkWIAD         INT,
			 @FromMarkActual       INT,
			 @FromDutyName	       VARCHAR(100),
			 @FromDutyDate         DATE,
			 @FromDutystatus	   INT,
			 @FromSPID             INT,
			 @FromDuration         INT,
			 @FromOverTimeHrs	   INT,
			 @FromStarttime        INT,
			 @FromEndTime          INT,
			 @FromDisplayName      NVARCHAR(100),
			 @FromStartTimeLocal	DATETIME,
			 @FromEndTimeLocal		DATETIME,
			 @FromDutyType			INT,
			 @FromAllocationsDutyID INT,
			 @FromAllocationsID		INT,
			 @FromIsOverrideOver12	BIT,
			 @FromBreakDuration		INT,
			 @FromDutyComments		VARCHAR(MAX);

   DECLARE   @ToIDay			   INT,
			 @ToIsHomeTeam         INT,
			 @ToMarkWIAD           INT,
			 @ToMarkActual	       INT,
			 @ToDutyTeamID         INT,
			 @ToDutyName		   VARCHAR(100),
			 @ToDutyDate           DATE,
			 @ToSPID               INT,
			 @ToDuration           INT,
			 @ToMarkedOvertime     INT,
			 @ToStarttime          INT,
			 @ToEndtime            INT,
			 @ToIsFreelancer       INT = 0,
			 @ToLeaveStartTime     INT,
			 @ToLeaveEndTime       INT,
			 @ToLeaveID            INT,
			 @ToDisplayName        VARCHAR(100),
			 @ToID                 INT,
			 @ToWeekNumber         INT,
			 @LeaveType				INT,
			 @LeaveStatus			INT,
			 @DutyStartTimeLocal	DATETIME,
			 @DutyEndTimeLocal		DATETIME,
			 @ChargingStatus		INT,
			 @DutyType				INT,
			 @AllocationsID			INT,
			 @AllocationsDutyID		INT,
			 @OverTwelveStatus		INT,
			 @IsOverrideOver12		INT,
			 @OverTimeHours			INT,
			 @DutyBreakTime			INT,
			 @MarkedOverTime		INT;

   DECLARE @TempAllocation	TABLE (AllocationID INT);
   DECLARE @AllocationsSPIDList TABLE (AllocationsSPID INT);

   DECLARE  @TempHistory	TABLE (HistoryID INT,
							       AttributeID INT,
								   HistorySubType VARCHAR(10),
								   HistoryType INT,
								   UserID INT,
								   History NVARCHAR(MAX),
								   CreateDateTime datetime );


      SELECT @vname =  UD_DisplayName,
			 @vuserID = UD_UserID
	    FROM UserDetails
	   WHERE UD_NetLogin = @pNetLogin

	   SELECT @FromDisplayName = UD_DisplayName
	    FROM UserDetails
	   WHERE UD_UserID = @SchduledPersonID

    BEGIN TRY
      BEGIN TRAN
	    IF ( ISNULL(@SchduledPersonID,0) = 0 )
	     BEGIN

			INSERT INTO AllocationsDuties
				  ( AD_AllocationsID,
					AD_DutyName,
					AD_Duration,
					AD_iDay,
					AD_StartTimeSec,
					AD_EndTimeSec,
					AD_DutyBreakTime,
					AD_DutyDate,
					AD_DutyStartTimeUTC,
					AD_DutyEndTimeUTC,
					AD_DutyStartTimeLocal,
					AD_DutyEndTimeLocal,
					AD_MasterDutyID,
					AD_DutyType,
					AD_DutyStatus,
					AD_DutyColourID,
					AD_Comments,
					AD_isAttention,
					AD_isRequest,
					AD_DutyProgramID1,
					AD_DutyProgramID2,
					AD_DutyProgramID3,
					AD_DutyProgramID4,
					AD_DutyProgramID5,
					AD_DutyProgramID6,
					AD_PlannedDuration,
					AD_PlannedDutyBreakTime,
					AD_IsNeedCovering,
					AD_IsOverrideOver12,
					AD_CreatedBy,
					AD_CreatedDate
				  )
			OUTPUT INSERTED.AD_AllocationsDutyID INTO @TempAllocation
			SELECT  AL_AllocationsID,
					AD_DutyName,
					AD_Duration,
					TD.ixDayInWeek,
					AD_StartTimeSec,
					AD_EndTimeSec,
					AD_DutyBreakTime,
					TD.dDateTime,
					CASE
						WHEN AD_StartTimeSec IS NULL THEN NULL
						WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec = 0 THEN NULL
						WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec > 0 THEN td.dDateTime
						WHEN AD_StartTimeSec > 0 THEN dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec)
						ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec) END AS StartDateUTC,
					CASE
						WHEN AD_EndTimeSec IS  NULL THEN NULL
						WHEN AD_StartTimeSec = 0 AND AD_EndTimeSec = 0 THEN NULL
						WHEN AD_StartTimeSec > 0 AND AD_EndTimeSec = 0 THEN DATEADD(DAY,1,td.dDateTime)
						WHEN AD_EndTimeSec = 86400 THEN DATEADD(DAY,1,td.dDateTime)
						WHEN AD_EndTimeSec > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec-86400)
						WHEN AD_EndTimeSec < AD_StartTimeSec THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec)
						ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_EndTimeSec) END AS EndDateUTC,
					CASE
						WHEN AD_StartTimeSec IS NULL THEN NULL
						WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec = 0 THEN NULL
						WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec > 0 THEN td.dDateTime
						WHEN AD_StartTimeSec > 0 THEN dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec)
						ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec) END AS StartDateLocal,
					CASE
						WHEN AD_EndTimeSec IS  NULL THEN NULL
						WHEN AD_StartTimeSec = 0 AND AD_EndTimeSec = 0 THEN NULL
						WHEN AD_StartTimeSec > 0 AND AD_EndTimeSec = 0 THEN DATEADD(DAY,1,td.dDateTime)
						WHEN AD_EndTimeSec = 86400 THEN DATEADD(DAY,1,td.dDateTime)
						WHEN AD_EndTimeSec > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec-86400)
						WHEN AD_EndTimeSec < AD_StartTimeSec THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec)
						ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_EndTimeSec) END AS EndDateLocal,
					AD_MasterDutyID,
					AD_DutyType,
					0,
					AD_DutyColourID,
					AD_Comments,
					0 AS AD_isAttention,
					0 AS AD_isRequest,
					AD_DutyProgramID1,
					AD_DutyProgramID2,
					AD_DutyProgramID3,
					AD_DutyProgramID4,
					AD_DutyProgramID5,
					AD_DutyProgramID6,
					AD_PlannedDuration,
					AD_PlannedDutyBreakTime,
					AD_IsNeedCovering,
					AD_IsOverrideOver12,
					@vuserID,
					getutcdate()
				FROM Allocations AL
				INNER JOIN TimeDimension TD ON AL_WeekNumber = td.ixYearWeek
				INNER JOIN AllocationsDuties AD on 1 =1
				WHERE AL_SchedulingTeamID = @TeamID
				  AND AL_WeekNumber = TD.ixYearWeek
				  AND TD.dDateTime BETWEEN @FromDate and @ToDate
				  AND AD_AllocationsDutyID = @CopyDutyID


				INSERT INTO AllocationsJobs
						(   AJ_AllocationsDutyID,
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
							AJ_CreatedDate )
				SELECT		TL.AllocationID,
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
							@vuserID,
							GETUTCDATE()
				  FROM @TempAllocation TL
				INNER JOIN AllocationsJobs AJ on 1 = 1
				WHERE AJ.AJ_AllocationsDutyID = @CopyDutyID
				  AND Aj.AJ_JobStatus = 1

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
				   TAL.AllocationID AS attributeid,
				   getdate(),
				   @vuserID,
				   'Duty copied '
				   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
				   + FORMAT(Getdate(),'HH:mm')
				   + ' By ' + @vname
				   + ' in unallocated section. Duty: '
				   + AD_DutyName
				   +'.',
				   'DH'
			  FROM @TempAllocation TAL
			  INNER JOIN AllocationsDuties AD ON TAL.AllocationID = AD_AllocationsDutyID
			  INNER JOIN HistoryTypes HT ON 1 = 1
			  WHERE  historytype = 'AllocationDuty'

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
				   'New Job created by '
				   +@vname+' On '
				   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +'.'
			FROM  AllocationsJobs aj
			INNER JOIN @TempAllocation al ON al.AllocationID = aj.AJ_AllocationsDutyID
			INNER JOIN historytypes HT ON 1 = 1
			WHERE historytype='AllocationJobs'

			INSERT INTO AllocationsUpdated
			      ( AU_AllocationsID,
				    AU_AllocationsDutyID,
					AU_AllocationsSPID,
					AU_Status,
					AU_UpdatedBy,
					AU_UpdatedDate
				   )
			SELECT AD_AllocationsID,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   0,
				   @vuserID,
				   GETUTCDATE()
			  FROM @TempAllocation TA
			 INNER JOIN AllocationsDuties AD ON TA.AllocationID = AD.AD_AllocationsDutyID
			  LEFT JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID

	  END
	 ELSE
	  BEGIN

		  DECLARE @EditAllocationStatus TABLE (SPStatus INT,ErrorMsg VARCHAR(1000));
 		  DECLARE @vErrorMsg			VARCHAR(1000),
				  @vSPStatus			INT;

	      DECLARE @TempAllocations TABLE(	iDay				INT,
											IsHomeTeam			INT,
											MarkWIAD			INT,
											MarkActual			INT,
											DutyName			NVARCHAR(100),
											DutyTeamID			INT,
											SchedulingPersonID	INT,
											DutyDate			DATE,
											duration			INT,
											StartTime			INT,
											EndTime				INT,
											DisplayName			NVARCHAR(100),
											LeaveStartTime		INT,
											LeaveEndTime		INT,
											LeaveID				INT,
											AllocationsSPID 	INT,
											WeekNumber 	INT,
											LeaveType 	INT,
											LeaveStatus 	INT,
											DutyStartTimeLocal 	DATETIME,
											DutyEndTimeLocal	DATETIME,
											ChargingStatus 	INT,
											DutyType 	INT,
											AllocationsID 	INT,
											AllocationsDutyID 	INT,
											OverTwelveStatus 	INT,
											IsOverrideOver12 	INT,
											OverTimeHours 	INT,
											DutyBreakTime 	INT,
											MarkedOverTime  	INT )


				 SELECT @FromIDay        = AD_iDay,
				        @FromIsHomeTeam  = 1,
						@FromMarkWIAD    = 0,
						@FromMarkActual  = 0,
						@FromDutyName    = AD_DutyName,
						@FromDutyDate    = AD_DutyDate,
						@FromDuration	 = AD_Duration,
						@FromStartTime   = AD_StartTimeSec,
						@FromEndTime     = AD_EndTimeSec,
						@FromDutystatus	 = AD_DutyStatus,
						@FromStartTimeLocal	= AD_DutyStartTimeLocal,
						@FromEndTimeLocal	= AD_DutyEndTimeLocal,
						@FromAllocationsID		= AL_AllocationsID,
						@FromIsOverrideOver12 = AD_IsOverrideOver12,
						@FromAllocationsDutyID = AD_AllocationsDutyID,
						@FromOverTimeHrs	   = 0,
						@FromBreakDuration	   = AD_DutyBreakTime,
						@FromDutyComments	   = AD_Comments
				   FROM Allocations AL
				  INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
				  WHERE AD_AllocationsDutyID = @CopyDutyID

			BEGIN

				 INSERT INTO @TempAllocations
				 SELECT ASP_iDay,
				        1 as IsHomeTeam,
						CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END as MarkWIAD,
						CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END AS MarkActual,
						AD_DutyName				AS DutyName,
						ASP_DutyTeamID			AS DutyTeamID,
						ASP_SchedulingPersonID	AS SchedulingPersonID,
						ASP_DutyDate			AS DutyDate,
						AD_Duration				AS duration,
						AD_StartTimeSec			AS StartTime,
						AD_EndTimeSec			AS EndTime,
						UD_DisplayName			AS DisplayName,
						ASP_LeaveStartTimeSec	AS LeaveStartTime,
						ASP_LeaveEndTimeSec		AS LeaveEndTime,
						0						AS LeaveID,
						ASP_AllocationsSPID		AS ID,
						AL1.AL_WeekNumber			AS weeknumber,
						ASP_LeaveType,
						ASP_LeaveStatus,
						AD_DutyStartTimeLocal,
						AD_DutyEndTimeLocal,
						ISNULL(ASP_ChargingStatus,0),
						AD_DutyType,
						AL1.AL_AllocationsID,
						AD_AllocationsDutyID,
						ASP_OverTwelveStatus,
						AD_IsOverrideOver12,
						ASP_OverTimeHours,
						AD_DutyBreakTime,
						ASP_MarkedOverTime
				   FROM Allocations AL1
				  INNER JOIN TimeDimension TD on TD.ixYearWeek = AL_WeekNumber
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsID = AL_AllocationsID
															AND TD.ixDayInWeek = ASP_iDay
				  INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE TD.dDateTime BETWEEN @FromDate and @ToDate
				    AND AL1.AL_SchedulingTeamID = @TeamID
					AND UD_UserID = @SchduledPersonID
				UNION
				 SELECT ASP_iDay,
				        0 AS IsHomeTeam,
						CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END as MarkWIAD,
						CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END AS MarkActual,
						AD_DutyName				AS DutyName,
						ASP_DutyTeamID			AS DutyTeamID,						
						ASP_SchedulingPersonID	AS SchedulingPersonID,
						ASP_DutyDate			AS DutyDate,
						AD_Duration				AS duration,
						AD_StartTimeSec			AS StartTime,
						AD_EndTimeSec			AS EndTime,
						UD_DisplayName			AS DisplayName,
						ASP_LeaveStartTimeSec	AS LeaveStartTime,
						ASP_LeaveEndTimeSec		AS LeaveEndTime,
						0						AS LeaveID,
						ASP_AllocationsSPID		AS ID,
						AL1.AL_WeekNumber			AS weeknumber,
						ASP_LeaveType,
						ASP_LeaveStatus,
						AD_DutyStartTimeLocal,
						AD_DutyEndTimeLocal,
						ISNULL(ASP_ChargingStatus,0),
						AD_DutyType,
						AL1.AL_AllocationsID,
						AD_AllocationsDutyID,
						ASP_OverTwelveStatus,
						AD_IsOverrideOver12,
						ASP_OverTimeHours,
						AD_DutyBreakTime,
						ASP_MarkedOverTime 
				   FROM Allocations AL1 
				  INNER JOIN TimeDimension TD on TD.ixYearWeek = AL_WeekNumber	
				  INNER JOIN AllocationsAddPersons AP ON AP.AAP_AllocationsID = AL1.AL_AllocationsID
													AND  TD.ixDayInWeek = AP.AAP_iDay
				  INNER JOIN AllocationsScheduledPersons ASP on AP.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
				  INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = AAP_SchedulingPersonID				  
				  WHERE TD.dDateTime BETWEEN @FromDate and @ToDate
				    AND AL1.AL_SchedulingTeamID = @TeamID
					AND UD_UserID = @SchduledPersonID

				IF EXISTS  (	SELECT TOP 1 * FROM @TempAllocations )
				  SET @CopyFlag = 1

				IF EXISTS ( SELECT 1
				              FROM @TempAllocations AL
							 WHERE (ISNULL(AL.LeaveStartTime,0) > 0 OR ISNULL(AL.LeaveEndTime,0) > 0 )
							   AND ISNULL(LeaveStatus,0) <> 9
						  )
				 BEGIN
				  THROW 51023, 'You cannot copy to a day containing part day leave.', 1;
				 END

				IF EXISTS ( SELECT 1
				              FROM @TempAllocations AL
							 WHERE ISNULL(AL.MarkedOvertime,0) = 1
						  )
				 BEGIN
				  THROW 51023, 'Duties cannot be copied onto days with Charging, or those marked as Absent, Sick, Leave or Overtime.', 1;
				 END

				IF EXISTS ( SELECT 1
				              FROM @TempAllocations AL
							 WHERE ChargingStatus > 0
						  )
				 BEGIN
				  THROW 51022, 'Duties cannot be copied onto days with Charging, or those marked as Absent, Sick, Leave or Overtime.', 1;
				 END;

                IF EXISTS ( SELECT  1
							  FROM LeaveApplications LA
							 INNER JOIN @TempAllocations TL ON TL.SchedulingPersonID = LA.SchedulingPersonID
															AND TL.DutyDate = LA.dDate
							 WHERE LA.Deleted = 0 
							   AND ( ISNULL(LA.LeaveStartTime,0) > 0
							       OR ISNULL(LA.LeaveEndTime,0) > 0)
						   )
				 BEGIN
				   THROW 51024, 'You cannot copy to a day containing part day leave', 1;
				 END;



				DECLARE CUR_Dest CURSOR FOR
				 SELECT iDay,
						IsHomeTeam,
						MarkWIAD,
						MarkActual,
						DutyName,
						DutyTeamID,
						SchedulingPersonID,
						DutyDate,
						duration,
						StartTime,
						EndTime,
						DisplayName,
						LeaveStartTime,
						LeaveEndTime,
						LeaveID,
						AllocationsSPID,
						WeekNumber,
						LeaveType,
						LeaveStatus,
						DutyStartTimeLocal,
						DutyEndTimeLocal,
						ChargingStatus,
						DutyType,
						AllocationsID,
						AllocationsDutyID,
						OverTwelveStatus,
						IsOverrideOver12,
						OverTimeHours,
						DutyBreakTime,
						MarkedOverTime
				 FROM @TempAllocations

				OPEN CUR_Dest

	            FETCH NEXT FROM CUR_Dest INTO   @ToIDay,
												@ToIsHomeTeam,
												@ToMarkWIAD,
												@ToMarkActual,
												@ToDutyName,
												@ToDutyTeamID,
												@ToSPID,
												@ToDutyDate,
												@ToDuration,
												@ToStarttime,
												@ToEndtime,
												@ToDisplayName,
												@ToLeaveStartTime,
												@ToLeaveEndTime,
												@ToLeaveID,
												@ToID,
												@ToWeekNumber,
												@LeaveType,
												@LeaveStatus,
												@DutyStartTimeLocal,
												@DutyEndTimeLocal,
												@ChargingStatus,
												@DutyType,
												@AllocationsID,
												@AllocationsDutyID,
												@OverTwelveStatus,
												@IsOverrideOver12,
												@OverTimeHours,
												@DutyBreakTime,
												@MarkedOverTime;

		     WHILE @@FETCH_STATUS = 0
			  BEGIN

				IF ( ISNULL(@FromStartTime,0) > 0 AND @ToDutyDate =  @FromDate )
				 BEGIN

				    SELECT @PrevEndTime = IsDutyOverLap ,
						   @Message = PrevDayOvrlapMsg
					  FROM dbo.ufn_check_DutyOverLap_PrevDay(@ToID,DATEADD(DAY,
												@MidnightFlag + -1 * DATEDIFF(DAY,@ToDutyDate,@FromStartTimeLocal),@FromStartTimeLocal))

					IF ( ISNULL(@PrevEndTime,0) > 0 )
					 BEGIN
					   THROW 51026, @Message , 1;
					 END;
				 END

				IF ( ISNULL(@FromEndTime,0) > 0 AND @ToDutyDate =  @ToDate )
				 BEGIN

				    SET @MidnightFlag = CASE WHEN DATEDIFF(DAY,@FromStartTimeLocal,@FromEndTimeLocal) > 0
											 THEN 1
											 ELSE 0
									    END

				    SELECT @NextStartTime = IsDutyOverLap ,
						   @Message = NextDayOvrlapMsg
					  FROM dbo.ufn_check_DutyOverLap_NextDay(@ToID,DATEADD(DAY,
												@MidnightFlag + -1*DATEDIFF(DAY,@ToDutyDate,@FromEndTimeLocal),@FromEndTimeLocal))

					IF ( ISNULL(@NextStartTime,0) > 0 )
					 BEGIN
					   THROW 51027, @Message , 1;
					 END;
                END;

			    IF ( ISNULL(@ToDutyTeamID,0) > 0 AND ( ISNULL(@ToDutyTeamID,0) <>  @TeamID )
					 AND @DutyType < 7 )
				 BEGIN
				   THROW 51029, 'It is not possible to swap a duty with one belonging to another team ', 1;
				 END

			    IF (  (ISNULL(@ToIsHomeTeam,1) = 1
					   and ( ISNULL(@ToMarkWIAD,0) = 1 or ISNULL(@ToMarkActual,0) = 1) ) )
				 BEGIN
				  THROW 51030, 'This Scheduled Person is assigned to a different team on this day', 1;
				 END

			    SET @IsFreelancer = dbo.ufn_IsFreeLancer(@ToSPID,@ToDutyDate)

			    IF ( @IsFreelancer = 0
				     AND ( ISNULL(@ToIsHomeTeam,1) IN (0,2)
					   and ( ISNULL(@ToMarkWIAD,0) = 0 AND ISNULL(@ToMarkActual,0) = 0) ) )
				 BEGIN
				  THROW 51031, 'This Scheduled Person is assigned to a different team on this day ', 1;
				 END;

                IF (  ISNULL(@LeaveType,0) > 0 AND ISNULL(@LeaveStatus,0) = 1)
                 BEGIN
				  THROW 51032, 'Cannot assign a Duty to Absent/Sick/Leave Days ', 1;
                 END;

					INSERT INTO @TempHistory ( historytype,
							  attributeid,
							  HistorySubType,
							  CreateDateTime,
							  userid,
							  history )
					SELECT ht.id AS historytype,
						   @ToID AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Over12 was Removed by system '+
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'
					  FROM HistoryTypes HT
					 WHERE ISNULL(@OverTwelveStatus,0) > 1
					   AND HT.historytype = 'AllocationScheduledPerson'


	            FETCH NEXT FROM CUR_Dest INTO   @ToIDay,
												@ToIsHomeTeam,
												@ToMarkWIAD,
												@ToMarkActual,
												@ToDutyName,
												@ToDutyTeamID,
												@ToSPID,
												@ToDutyDate,
												@ToDuration,
												@ToStarttime,
												@ToEndtime,
												@ToDisplayName,
												@ToLeaveStartTime,
												@ToLeaveEndTime,
												@ToLeaveID,
												@ToID,
												@ToWeekNumber,
												@LeaveType,
												@LeaveStatus,
												@DutyStartTimeLocal,
												@DutyEndTimeLocal,
												@ChargingStatus,
												@DutyType,
												@AllocationsID,
												@AllocationsDutyID,
												@OverTwelveStatus,
												@IsOverrideOver12,
												@OverTimeHours,
												@DutyBreakTime,
												@MarkedOverTime;


			   END;

			   CLOSE CUR_Dest;

			   DEALLOCATE CUR_Dest;

		  END;

		  BEGIN

		  		DECLARE CUR_NewSP CURSOR FOR
				SELECT * FROM
				( SELECT SL.ScheduledPersonID as SchedulingPersonID,
						 TD.dDateTime  as  DutyDate,
						 AllocationsSPID,
						 AL_AllocationsID,
						 AllocationsDutyID,
						 SL.IsHomeTeam
				 FROM Allocations AL
				INNER JOIN TimeDimension TD on TD.ixYearWeek = AL_WeekNumber
				INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = AL_SchedulingTeamID
				 LEFT JOIN @TempAllocations TL ON SL.ScheduledPersonID = TL.SchedulingPersonID
											  AND TD.ixYearWeek = TL.WeekNumber
											  AND TD.ixDayInWeek = TL.iDay
				WHERE TD.dDateTime BETWEEN SL.StartDate and SL.EndDate
				  AND TD.dDateTime BETWEEN @FromDate and @ToDate
				  AND AL_SchedulingTeamID = @TeamID
				  AND SL.ScheduledPersonID = @SchduledPersonID
				  AND SL.scheduledType = 1
				) FD WHERE AllocationsSPID IS NULL

				OPEN CUR_NewSP

	            FETCH NEXT FROM CUR_NewSP INTO  @ToSPID,
												@ToDutyDate,
												@ToID,
												@AllocationsID,
												@AllocationsDutyID,
												@IshomeTeam;

		     WHILE @@FETCH_STATUS = 0
			  BEGIN

		  		IF ( ISNULL(@ToID,0) = 0 AND @SchduledPersonID > 0 )
				 BEGIN

				  IF ( @IshomeTeam <> 1 )
				    SET @IsFreelancer = dbo.ufn_IsFreeLancer(@ToSPID,@ToDutyDate)

				  IF ( @IshomeTeam IN (0,2) AND ISNULL(@IsFreelancer,0) = 0 )
				   BEGIN
				     THROW 51035, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END

				  IF ( ( @IshomeTeam = 1 OR  ISNULL(@IsFreelancer,0) = 1) AND @AllocationsID > 0 )
				   BEGIN

				    SET @CopyFlag = 1

					EXEC @ReturnValue =  usp_CreateScheduledPerson  @AllocationsID,
													0,
													@SchduledPersonID,
													@ToDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;

				    INSERT INTO @AllocationsSPIDList VALUES ( @AllocationsSPID)

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while the creating scheduled person allocation', 1;
					END
				  END

				 END;

	            FETCH NEXT FROM CUR_NewSP INTO  @ToSPID,
												@ToDutyDate,
												@ToID,
												@AllocationsID,
												@AllocationsDutyID,
												@IshomeTeam;

			   END

			   CLOSE CUR_NewSP;

			   DEALLOCATE CUR_NewSP;

			 END;

			IF (  ISNULL(@CopyFlag,0) = 0 )
			  BEGIN
				THROW 51021, 'Allocations for this week is not created yet', 1;
			  END;

			INSERT INTO @TempHistory
					(
						historytype,
						attributeid,
						HistorySubType,
						CreateDateTime,
						userid,
						history
					)
			SELECT ht.id AS historytype,
					AllocationsDutyID AS attributeid,
					'DH' as HistorySubType,
					getdate(),
					@vuserID,
					'Modified '
					+ ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
                        + FORMAT(Getdate(),'HH:mm')
					+ ' By ' + @vname + ', Drag and Drop from Allocated Duties ( Unassigned '
					+  DutyName
					+ ' from '
					+ @FromDisplayName
					+' )'
					FROM @TempAllocations TL
				  INNER JOIN HistoryTypes HT ON 1 = 1
				  WHERE ISNULL(Duration,0) > 0
					AND (ISNULL(StartTime,0) > 0 OR ISNULL(EndTime,0) > 0 )
					AND DutyType < 7
					AND ht.historytype = 'AllocationDuty'

				  UPDATE AD
				     SET AD_DutyStatus = CASE WHEN ISNULL(AD_Duration,0) = 0 
						 THEN 9
						 WHEN ISNULL(AD_Duration,0) > 0 
						 THEN 0
					     END,
						 ad.AD_IsNeedCovering = 1,
						 AD_UpdatedBy = @vuserID,
						 AD_UpdatedDate = GETUTCDATE()
					FROM @TempAllocations TL
				  INNER JOIN AllocationsDuties AD on TL.AllocationsDutyID =  AD_AllocationsDutyID
				  WHERE DutyType NOT IN (7,8,11,12)

				INSERT INTO AllocationsDuties
					  ( AD_AllocationsID,
						AD_DutyName,
						AD_Duration,
						AD_iDay,
						AD_StartTimeSec,
						AD_EndTimeSec,
						AD_DutyBreakTime,
						AD_DutyDate,
						AD_DutyStartTimeUTC,
						AD_DutyEndTimeUTC,
						AD_DutyStartTimeLocal,
						AD_DutyEndTimeLocal,
						AD_MasterDutyID,
						AD_DutyType,
						AD_DutyStatus,
						AD_DutyColourID,
						AD_isAttention,
						AD_isRequest,
						AD_DutyProgramID1,
						AD_DutyProgramID2,
						AD_DutyProgramID3,
						AD_DutyProgramID4,
						AD_DutyProgramID5,
						AD_DutyProgramID6,
						AD_PlannedDuration,
						AD_PlannedDutyBreakTime,
						AD_IsNeedCovering,
						AD_IsOverrideOver12,
						AD_CreatedBy,
						AD_CreatedDate
					  )
				OUTPUT INSERTED.AD_AllocationsDutyID INTO @TempAllocation
				SELECT  AL_AllocationsID,
						AD_DutyName,
						AD_Duration,
						TD.ixDayInWeek,
						AD_StartTimeSec,
						AD_EndTimeSec,
						AD_DutyBreakTime,
						TD.dDateTime,
					    CASE
						 WHEN AD_StartTimeSec IS NULL THEN NULL
						 WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec = 0 THEN NULL
						 WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec > 0 THEN td.dDateTime
						 WHEN AD_StartTimeSec > 0 THEN dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec)
						 ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec) END AS StartDateUTC,
					    CASE
						 WHEN AD_EndTimeSec IS  NULL THEN NULL
						 WHEN AD_StartTimeSec = 0 AND AD_EndTimeSec = 0 THEN NULL
						 WHEN AD_StartTimeSec > 0 AND AD_EndTimeSec = 0 THEN DATEADD(DAY,1,td.dDateTime)
						 WHEN AD_EndTimeSec = 86400 THEN DATEADD(DAY,1,td.dDateTime)
						 WHEN AD_EndTimeSec > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec-86400)
						 WHEN AD_EndTimeSec < AD_StartTimeSec THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec)
						 ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_EndTimeSec) END AS EndDateUTC,
					    CASE
						 WHEN AD_StartTimeSec IS NULL THEN NULL
						 WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec = 0 THEN NULL
						 WHEN AD_StartTimeSec = 0 AND AD_StartTimeSec > 0 THEN td.dDateTime
						 WHEN AD_StartTimeSec > 0 THEN dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec)
						 ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_StartTimeSec) END AS StartDateLocal,
					    CASE
						 WHEN AD_EndTimeSec IS  NULL THEN NULL
						 WHEN AD_StartTimeSec = 0 AND AD_EndTimeSec = 0 THEN NULL
						 WHEN AD_StartTimeSec > 0 AND AD_EndTimeSec = 0 THEN DATEADD(DAY,1,td.dDateTime)
						 WHEN AD_EndTimeSec = 86400 THEN DATEADD(DAY,1,td.dDateTime)
						 WHEN AD_EndTimeSec > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec-86400)
						 WHEN AD_EndTimeSec < AD_StartTimeSec THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime),AD_EndTimeSec)
						 ELSE dbo.ufn_ConvertToDateTime(td.dDateTime,AD_EndTimeSec) END AS EndDateLocal,
						AD_MasterDutyID,
						AD_DutyType,
						1,
						AD_DutyColourID,
						0 AS AD_isAttention,
						0 AS AD_isRequest,
						AD_DutyProgramID1,
						AD_DutyProgramID2,
						AD_DutyProgramID3,
						AD_DutyProgramID4,
						AD_DutyProgramID5,
						AD_DutyProgramID6,
						AD_PlannedDuration,
						AD_PlannedDutyBreakTime,
						AD_IsNeedCovering,
						AD_IsOverrideOver12,
						@vuserID,
						getutcdate()
					FROM Allocations AL
					INNER JOIN TimeDimension TD ON AL_WeekNumber = td.ixYearWeek
					INNER JOIN AllocationsDuties AD on 1 =1
					WHERE AL_SchedulingTeamID = @TeamID
					  AND AL_WeekNumber = TD.ixYearWeek
					  AND TD.dDateTime BETWEEN @FromDate and @ToDate
					  AND AD_AllocationsDutyID = @CopyDutyID

					INSERT INTO AllocationsJobs
							(   AJ_AllocationsDutyID,
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
								AJ_CreatedDate )
					SELECT		TL.AllocationID,
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
								@vuserID,
								GETUTCDATE()
					  FROM @TempAllocation TL
					INNER JOIN AllocationsJobs AJ on 1 = 1
					WHERE AJ.AJ_AllocationsDutyID = @CopyDutyID
					  AND Aj.AJ_JobStatus = 1

					UPDATE ASP
						SET ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID,
							ASP.ASP_DutyTeamID = AL_SchedulingTeamID,
							ASP_WIADStatus = CASE WHEN ISNULL(SL1.ScheduledPersonID,0) > 0 AND ISNULL(ASP_WIADStatus,0) = 0
												  THEN 2
												  ELSE ASP_WIADStatus 
											  END, 
							ASP_OverTwelveStatus =  CASE WHEN AD_Duration > 43200
														  AND ISNULL(AD_IsOverrideOver12,1) = 1
														 THEN 1 ELSE 0 END,
						    ASP_OverTwelveHrs = 0,
						    ASP_IsOverseasOverTwelve = 0,
							ASP.ASP_UpdatedBy = @vuserID,
							ASP.ASP_UpdatedDate = GETUTCDATE()
						FROM Allocations AL
					INNER JOIN TimeDimension TD ON AL_WeekNumber = td.ixYearWeek
					INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
													AND TD.ixDayInWeek = AD_iDay
					INNER JOIN @TempAllocation TL on TL.AllocationID = AD_AllocationsDutyID
					INNER JOIN AllocationsScheduledPersons ASP on ASP_DutyDate = AD_DutyDate
					 LEFT JOIN ( SELECT SL.ScheduledPersonID,
										SL.StartDate,
										SL.EndDate
					               FROM ScheduledPersonTeam_LINK SL
								  INNER JOIN schedulingTeams ST ON ST.schedulingTeamId = SL.TeamID
								  WHERE ST.schedulingTeamName IN ('Other BBC', 'Freelancers')
														AND SL.scheduledType = 1
									AND SL.IsHomeTeam = 1 ) SL1 ON SL1.ScheduledPersonID = ASP.ASP_SchedulingPersonID 
														AND ASP.ASP_DutyDate BETWEEN SL1.StartDate and SL1.EndDate					 
					WHERE AL_SchedulingTeamID = @TeamID
					  AND TD.dDateTime BETWEEN @FromDate and @ToDate
					  AND ASP_SchedulingPersonID = @SchduledPersonID

					IF EXISTS ( SELECT 1
								  FROM @TempAllocations
								 WHERE AllocationsDutyID = @CopyDutyID )
					  BEGIN

					    UPDATE AD
						   SET AD.AD_Comments = @FromDutyComments
						  FROM AllocationsDuties AD
						 INNER JOIN @TempAllocation TL ON TL.AllocationID = AD.AD_AllocationsDutyID
						 WHERE AD.AD_DutyDate = @FromDutyDate
					   
					  END

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history
							)
					SELECT ht.id AS historytype,
						   tl.AllocationID AS attributeid,
						   'DH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified'
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
                           + ' By ' + @vname + ', copied duty to '
						   + @FromDisplayName
						   + ' (Swapped '
						   + CASE WHEN TLS.DutyName IS NULL THEN 'U' 
									ELSE TLS.DutyName END+' to '+@FromDutyName+ ' )'
					FROM   @TempAllocation TL
					INNER JOIN AllocationsDuties AD on TL.AllocationID = AD_AllocationsDutyID
					INNER JOIN AllocationsScheduledPersons ASP on ASP_AllocationsDutyID = AD_AllocationsDutyID
					LEFT JOIN @TempAllocations TLS on TLS.AllocationsSPID = ASP_AllocationsSPID
					INNER JOIN HistoryTypes HT on 1 = 1
					WHERE  historytype = 'AllocationDuty'

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history
							)
					SELECT ht.id AS historytype,
						   ASP_AllocationsSPID AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified'
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
                           + ' By ' + @vname + ', copied duty to '
						   + @FromDisplayName
						   + ' (Swapped '
						   + CASE WHEN TLS.DutyName IS NULL THEN 'U' 
									ELSE TLS.DutyName END+' to '+@FromDutyName+ ' )'
					FROM   @TempAllocation TL
					INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = TL.AllocationID
					INNER JOIN AllocationsScheduledPersons ASP on ASP_AllocationsDutyID = AD_AllocationsDutyID
					LEFT JOIN @TempAllocations TLS on TLS.AllocationsSPID = ASP_AllocationsSPID
					INNER JOIN HistoryTypes HT on 1 = 1
					WHERE  historytype = 'AllocationScheduledPerson'

				INSERT INTO @TempHistory
					  (
							historytype,
							attributeid,
							CreateDateTime,
							userid,
							history
					  )
				SELECT ht.id AS historytype,
					   aj.AJ_AllocateJobID AS attributeid,
					   getdate(),
					   @vuserID,
					   'New Job created by '
					   +@vname+' On '
					   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +'.'
				FROM  AllocationsJobs aj
				INNER JOIN @TempAllocation al ON al.AllocationID = aj.AJ_AllocationsDutyID
				INNER JOIN historytypes HT ON 1 = 1
				WHERE historytype='AllocationJobs'

						INSERT INTO History ( historytype,
								  attributeid,
								  HistorySubType,
								  [datetime],
								  userid,
								  history )
						SELECT historytype,
								  AttributeID,
								  HistorySubType,
								  CreateDateTime,
								  userid,
								  history
						  FROM @TempHistory


			EXEC @ReturnValue =  usp_Create_DutyAccPeriodSummarySP @FromDate, @ToDate, @SchduledPersonID, @pNetLogin

		  BEGIN

            DECLARE CUR_CheckWTD CURSOR FOR
			 SELECT ASP_AllocationsSPID
			   FROM AllocationsScheduledPersons asp
			  INNER JOIN @TempAllocation TL on TL.AllocationID = ASP_AllocationsDutyID

             OPEN CUR_CheckWTD

	         FETCH NEXT FROM CUR_CheckWTD INTO @ToID ;

		     WHILE @@FETCH_STATUS = 0
			  BEGIN
			    EXEC @return_value = usp_UpdateWTD @ToID, @pNetLogin
			    FETCH NEXT FROM CUR_CheckWTD INTO @ToID ;
			  END

			 CLOSE CUR_CheckWTD;
			 DEALLOCATE CUR_CheckWTD;

		  END

			INSERT INTO AllocationsUpdated
			      ( AU_AllocationsID,
				    AU_AllocationsDutyID,
					AU_AllocationsSPID,
					AU_Status,
					AU_UpdatedBy,
					AU_UpdatedDate
				   )
			SELECT AD_AllocationsID,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   0,
				   @vuserID,
				   GETUTCDATE()
			  FROM @TempAllocation TA
			 INNER JOIN AllocationsDuties AD ON TA.AllocationID = AD.AD_AllocationsDutyID
			  LEFT JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID

			INSERT INTO AllocationsUpdated
			      ( AU_AllocationsID,
				    AU_AllocationsDutyID,
					AU_AllocationsSPID,
					AU_Status,
					AU_UpdatedBy,
					AU_UpdatedDate
				   )
			SELECT AD_AllocationsID,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   0,
				   @vuserID,
				   GETUTCDATE()
			  FROM @TempAllocations TA
			 INNER JOIN AllocationsDuties AD ON TA.AllocationsDutyID = AD.AD_AllocationsDutyID
			  LEFT JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID

			INSERT INTO AllocationsUpdated
			      ( AU_AllocationsID,
				    AU_AllocationsDutyID,
					AU_AllocationsSPID,
					AU_Status,
					AU_UpdatedBy,
					AU_UpdatedDate
				   )
			SELECT AD_AllocationsID,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   0,
				   @vuserID,
				   GETUTCDATE()
			  FROM @AllocationsSPIDList TA
			 INNER JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsDutyID = TA.AllocationsSPID
			 INNER JOIN AllocationsDuties AD ON ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID

	  END

		IF ( @@TRANCOUNT	> 0 )
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
			@vuserID

       IF ( ERROR_NUMBER() in (1204,1205,1222,3930) )
		 SELECT 'Somebody else is also editing this duty. Please try again' AS errorMessage, 0 spStatus
	    ELSE
	 	 SELECT ISNULL(@@IDENTITY,ERROR_NUMBER()) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage


	END CATCH;

END