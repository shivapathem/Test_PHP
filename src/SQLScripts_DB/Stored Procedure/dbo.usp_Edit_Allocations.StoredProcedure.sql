USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_Edit_Allocations]    Script Date: 30/03/2026 14:27:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                 PROCEDURE [dbo].[usp_Edit_Allocations]
@EditType 				VARCHAR(30),
@pNetLogin				VARCHAR(30),
@FromID  				INT = NULL,
@ToID					INT = NULL,
@pWeekNumber			INT = NULL,
@pTeamID				INT = NULL,
@pSchedulingPersonID	INT = NULL,
@pIsShiftleader			INT = NULL,
@pMarkWIAD				BIT = NULL,
@pMarkActual			BIT = NULL,
@pZeroLeave				INT = NULL,
@pLeaveID				INT = NULL,
@pDuration				INT = NULL,
@pLeaveHistory			VARCHAR(60) = NULL,
@pLeaveStartTime		INT = NULL,
@pLeaveEndTime			INT = NULL,
@pOverNightFlag			BIT = NULL,
@psicknessHistory		VARCHAR(100) = NULL,
@pMasterDutyID			INT = NULL,
@pAllocationsID			INT = NULL,
@pAllocationsSPID		INT = NULL,
@pAllocationsDutyID		INT = NULL,
@pDutyDate				DATE = NULL,
@pMarkForAttention		BIT = NULL,
@pMarkPurple			BIT = NULL,
@pMarkOverTime			BIT = NULL,
@pOverTimeHrs			INT = NULL,
@pCommentType			INT = NULL,  -- 0 Both Duty and Pers, 1 - Duty, 2 - Pers
@pPersonComments		VARCHAR(MAX) = NULL,
@pDutyComments			VARCHAR(MAX) = NULL,
@StartDate				DATE = NULL,
@EndDate				DATE = NULL
AS
BEGIN

   -- Edit Type Parameters are given below.
   -- SWAP               -> Swap two duties
   -- ASSIGN             -> Assign an unallocated duty to a person
   -- UNASSIGN           -> Unassign duty from a person
   -- ASSIGNMISCDUTY	 -> Assign Misc
   -- DELETEDUTY         -> Delete duty
   -- REMOVEFROMWEEK     -> Remove additional person from week
   -- ADDPERSON          -> Add additional Person to week
   -- MARKABSENT         -> Mark a person as Absent
   -- MARKSICK           -> Mark a person as Sick
   -- ASSGNTOADDPERSON   -> Assign Duty to Person Is Available No
   -- MARKWIAD           -> Mark WIAD for additional team
   -- MARKACTUAL         -> Mark Actual for additional team
   -- MARKLEAVE          -> Mark the Allocation as Leave


   SET NOCOUNT ON;
   SET DATEFORMAT YMD;

   DECLARE   @vname                 VARCHAR(50),
			 @vuserID               INT,
			 @FRSchedulingTeamId    INT,
			 @SchedulingTeamID	    INT,
			 @vWeekNumber		    INT,
			 @IsFreelancer          INT = 0,
			 @Message               VARCHAR(500),
			 @PublishStatus		    INT = 0,
			 @IsDutyOverLap	   		BIT,
			 @prevOverlapMsg		VARCHAR(500),
			 @nextOverlapMsg		VARCHAR(500),
			 @OvreLapMsg	      	VARCHAR(1000),
			 @AllocationsSPID		INT,
			 @AllocationsDutyID		INT,
			 @AllocationsID			INT,
			 @LeaveStartTimeLocal	DATETIME,
			 @LeaveEndTimeLocal		DATETIME,
			 @IsReturnData			BIT = 0,
			 @IsCreateHistory		BIT = 0,
			 @ScheduledpersonList	VARCHAR(100),
			 @ReturnValue			INT,
			 @HistoryType			INT;

   DECLARE	 @FromIDay             INT,
			 @FromIsHomeTeam       INT,
			 @FromMarkWIAD         INT,
			 @FromMarkActual       INT,
			 @FromDutyTeamID       INT,
			 @FromDutyName	       VARCHAR(100),
			 @FromDutyDate         DATE,
			 @FromDutystatus	   INT,
			 @FromSPID             INT,
			 @FromDuration         INT,
			 @FromMarkedOvertime   INT,
			 @FromOverTimeHrs	   INT,
			 @FromStarttime        INT,
			 @FromEndTime          INT,
			 @FromIsFreelancer     INT = 0,
			 @FromLeaveStartTime   INT,
			 @FromLeaveEndTime     INT,
			 @FromLeaveType        INT,
			 @FromDisplayName      NVARCHAR(100),
			 @FromLeaveStatus	   INT,
			 @FromStartTimeLocal	DATETIME,
			 @FromEndTimeLocal		DATETIME,
			 @FromChargingStatus	INT,
			 @FromDutyType			INT,
			 @FromAllocationsDutyID INT,
			 @FromMarkOverTweleve   INT,
			 @FromAllocationsID		INT,
			 @FromIsOverrideOver12	BIT,
			 @FromBreakDuration		INT,
			 @FromAllocationsAPID	INT;

   DECLARE   @ToIDay			   INT,
			 @ToIsHomeTeam         INT,
			 @ToMarkWIAD           INT,
			 @ToMarkActual	       INT,
			 @ToDutyTeamID         INT,
			 @ToDutyName		   VARCHAR(100),
			 @ToDutyDate           DATE,
			 @ToDutyStatus		   INT,
			 @ToSPID               INT,
			 @ToDuration           INT,
			 @ToMarkedOvertime     INT,
			 @ToStarttime          INT,
			 @ToEndtime            INT,
			 @ToIsFreelancer       INT = 0,
			 @ToLeaveStartTime     INT,
			 @ToLeaveEndTime       INT,
			 @ToLeaveType          INT,
			 @ToDisplayName        NVARCHAR(100),
			 @ToLeaveStatus			INT,
			 @ToStartTimeLocal		DATETIME,
			 @ToEndTimeLocal		DATETIME,
			 @ToChargingStatus		INT,
			 @ToDutyType			INT,
			 @ToAllocationsDutyID	INT,
			 @ToMarkOverTweleve     INT,
			 @ToAllocationsID		INT,
			 @ToIsOverrideOver12	BIT,
			 @ToOverTimeHrs			INT,
			 @ToBreakDuration		INT,
			 @ToAllocationsAPID		INT;

	  DECLARE @EditAllocationStatus TABLE (SPStatus INT,ErrorMsg VARCHAR(1000));

 	  DECLARE @vErrorMsg			VARCHAR(1000),
			  @vSPStatus			INT,
			  @FromDurationDutySummary	INT,
			  @ToDurationDutySummary	INT,
			  @AllocationsAPID			INT,
			  @RowCount					INT;

	  DECLARE @TempHistory	TABLE ( HistoryID INT,
									AttributeID INT,
									HistorySubType VARCHAR(10),
									HistoryType INT,
									UserID INT,
									History NVARCHAR(MAX),
									CreateDateTime datetime,
									ActionType VARCHAR(2))

   SELECT @vuserID = UD_UserID,
		  @vname = UD_DisplayName
     from UserDetails  (nolock)
	where UD_NetLogin = @pNetLogin

	SET @pIsShiftleader = CASE when ISNULL(@pIsShiftleader,0) = 1 THEN 1 ELSE 0 END
	SET @PublishStatus = CASE when ISNULL(@pIsShiftleader,0) = 1 THEN 1 ELSE 0 END

    BEGIN TRY
     BEGIN TRANSACTION

		------------------------------------------------
		-- SWAP Duties in Allocated grid
		------------------------------------------------

           IF  (@EditType = 'SWAP')
		    BEGIN

			    IF (@FromID IS NULL OR ( ISNULL(@ToID,0) = 0 AND ISNULL(@pSchedulingPersonID,0) = 0 ) )
				BEGIN
				  THROW 51000, 'From ID and To ID Cannot be NULL', 1;
				END

				 SELECT  @AllocationsID			 = AL_AllocationsID,
						 @SchedulingTeamID       = AL_SchedulingTeamID,
						 @vWeekNumber            = AL_WeekNumber
					FROM Allocations AL
				   WHERE AL_AllocationsID = @pAllocationsID;

				 SELECT @FromIDay               = ASP_iDay,
				        @FromIsHomeTeam         = CASE WHEN ASP_AllocationsID <> @AllocationsID
												  	   THEN 0
													   ELSE 1 END,
						@FromMarkWIAD           = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@FromMarkActual         = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@FromDutyName           = AD_DutyName,
						@FromDutyTeamID         = ASP_DutyTeamID,
						@FromDutyDate           = ASP_DutyDate,
						@FromSPID               = ASP_SchedulingPersonID,
						@FromDuration           = AD_Duration,
						@FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromDisplayName        = UD_DisplayName,
						@FromStarttime          = AD_StartTimeSec,
						@FromEndTime            = AD_EndTimeSec,
						@FromLeaveStartTime     = ASP_LeaveStartTimeSec,
						@FromLeaveEndTime       = ASP_LeaveEndTimeSec,
						@FromLeaveType          = ASP_LeaveType,
						@FromLeaveStatus        = ASP_LeaveStatus,
						@FromStartTimeLocal	    = AD_DutyStartTimeLocal,
						@FromEndTimeLocal	    = AD_DutyEndTimeLocal,
						@FromChargingStatus     = ASP_ChargingStatus,
						@FromDutyType           = AD_DutyType,
						@FromAllocationsID      = ASP_AllocationsID,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus,
						@FromIsOverrideOver12	= AD_IsOverrideOver12,
						@FromOverTimeHrs		= ASP_OverTimeHours,
						@FromBreakDuration	    = AD_DutyBreakTime
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @FromID;

				IF ( @AllocationsID <> @FromAllocationsID )
				  BEGIN

				     SELECT @FromAllocationsAPID = AP.AAP_AllocationsAPID
					  FROM AllocationsAddPersons AP
					 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_SchedulingPersonID = AP.AAP_SchedulingPersonID
															   AND ASP.ASP_DutyDate = AP.AAP_DutyDate
					  WHERE ASP.ASP_AllocationsID = @FromAllocationsID
					    AND ASP.ASP_AllocationsSPID = @FromID
						AND AP.AAP_AllocationsID = @AllocationsID

						EXEC @ReturnValue = usp_Create_HistoryAddPerson @FromAllocationsAPID

				  END

				/*IF ( @pIsShiftleader <> 1 AND ISNULL(@FromChargingStatus,0) <> 0 )
				 BEGIN
					THROW 51000, 'This duty cannot be deleted/swapped as it has one or more Charging records associated with it.
					Please remove the Charging records before trying to delete', 1;
				 END*/

                IF ( @FromDutyType IN (8,11,12) )
			  	 BEGIN
				   SET @Message = 'Cannot SWAP Duty ['+@FromDutyName+'] with other duty'
				 END;

                IF ( @FromDutyType IN (8,11,12) )
			  	 BEGIN
				   THROW 51002, @Message, 1;
				 END;

                IF ( @FromDutyType IN ( 7,9) )
				 BEGIN
				   THROW 51002, 'There is problem with the data. Underlying data has been modified. Please refresh the page and try again.', 1;
				 END;

                IF ( @FromLeaveType = 6 AND ISNULL(@FromLeaveStatus,0) <> 9 )
				 BEGIN
				   THROW 51003, 'You cannot swap/assign duty containing part day leave', 1;
				 END;

                IF ( ISNULL(@FromMarkedOvertime,0) > 0 )
				 BEGIN
				   THROW 51004, 'You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty  ', 1;
				 END;

				SET @FromIsFreelancer = dbo.ufn_isfreelancer(@FromSPID,@FromDutyDate)

			    IF ( ISNULL(@FromDutyTeamID,0) <> 0 AND  ISNULL(@FromDutyTeamID,0) <>  @SchedulingTeamID AND @FromDutyType NOT IN ( 7,9 ) )
				 BEGIN
				  IF ( @pIsShiftleader = 0 )
                   BEGIN
				     THROW 51005, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				  IF ( @pIsShiftleader = 1 AND ISNULL(@FromDuration,0) <> 0 )
                   BEGIN
				     THROW 51005, 'It  is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				 END

				IF ( ISNULL(@ToID,0) = 0 AND @pSchedulingPersonID > 0 )
				 BEGIN

					EXEC @ReturnValue =  usp_CreateScheduledPerson  @AllocationsID,
													0,
													@pSchedulingPersonID,
													@FromDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					END

					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

					SET @ToID = @AllocationsSPID;

				 END;

				 SELECT @ToIDay              = ASP_iDay,
				        @ToIsHomeTeam        = CASE WHEN ASP_AllocationsID <> @AllocationsID
													 THEN 0
													 ELSE 1 END,
						@ToMarkWIAD          = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@ToMarkActual        = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@ToDutyName          = AD_DutyName,
						@ToDutyTeamID        = ASP_DutyTeamID,
						@ToDutyDate          = ASP_DutyDate,
						@ToSPID              = ASP_SchedulingPersonID,
						@ToDuration          = AD_Duration,
						@ToMarkedOvertime    = ASP_MarkedOverTime,
						@ToDisplayName       = UD_DisplayName,
						@ToStarttime         = AD_StartTimeSec,
						@ToEndTime           = AD_EndTimeSec,
						@ToLeaveStartTime    = ASP_LeaveStartTimeSec,
						@ToLeaveEndTime      = ASP_LeaveEndTimeSec,
						@ToLeaveType         = ASP_LeaveType,
						@ToLeaveStatus       = ASP_LeaveStatus,
						@ToStartTimeLocal	 = AD_DutyStartTimeLocal,
						@ToEndTimeLocal	     = AD_DutyEndTimeLocal,
						@ToChargingStatus    = ASP_ChargingStatus,
						@ToDutyType          = AD_DutyType,
						@ToAllocationsID	 = ASP_AllocationsID,
						@ToAllocationsDutyID = AD_AllocationsDutyID,
						@ToMarkOverTweleve   = ASP_OverTwelveStatus,
						@ToIsOverrideOver12	 = AD_IsOverrideOver12,
						@ToOverTimeHrs		 = ASP_OverTimeHours,
						@ToBreakDuration	    = AD_DutyBreakTime
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @ToID;

				IF ( @AllocationsID <> @ToAllocationsID )
				  BEGIN

				     SELECT @ToAllocationsAPID = AP.AAP_AllocationsAPID
					  FROM AllocationsAddPersons AP
					 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_SchedulingPersonID = AP.AAP_SchedulingPersonID
															   AND ASP.ASP_DutyDate = AP.AAP_DutyDate
					  WHERE ASP.ASP_AllocationsID = @ToAllocationsID
					    AND ASP.ASP_AllocationsSPID = @ToID
						AND AP.AAP_AllocationsID = @AllocationsID

					  EXEC @ReturnValue = usp_Create_HistoryAddPerson @ToAllocationsAPID

				  END

				IF ( @pIsShiftleader <> 1 AND ISNULL(@ToChargingStatus,0) <> 0)
				BEGIN
				  THROW 51005, 'This duty cannot be deleted/swapped as it has one or more Charging records associated with it.
						Please remove the Charging records before trying to delete', 1;
				END

                IF ( @ToDutyType IN (8,11,12) )
			  	 BEGIN
				   SET @Message = 'Cannot SWAP Duty ['+@FromDutyName+'] with other duty'
				 END;

                IF ( @ToDutyType IN (8,11,12) )
			  	 BEGIN
				   THROW 51006, @Message, 1;
				 END;

                IF ( @ToLeaveType = 6 AND ISNULL(@ToLeaveStatus,0) <> 9 )
				 BEGIN
				   THROW 51007, 'You cannot swap/assign duty containing part day leave', 1;
				 END;

                IF ( ISNULL(@ToMarkedOvertime,0) > 0 )
				 BEGIN
				   THROW 51008, 'You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty  ', 1;
				 END;

				IF ( @Fromiday <> @Toiday )
				 BEGIN
				   THROW 51013, 'Only same Day duty can be swapped ', 1;
				 END;

				SET @ToIsFreelancer = dbo.ufn_isfreelancer(@ToSPID,@FromDutyDate)

			    IF ( ISNULL(@ToDutyTeamID,0) <> 0 AND  ISNULL(@ToDutyTeamID,0) <>  @SchedulingTeamID AND @ToDutyType NOT IN ( 7,9 ))
				BEGIN
				  IF ( @pIsShiftleader = 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				  IF ( @pIsShiftleader = 1 AND ISNULL(@ToDuration,0) <> 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				END

				IF ( @FromAllocationsID <> @ToAllocationsID AND @ToIsFreelancer = 0 AND @ToMarkWIAD = 0 AND @ToMarkActual = 0
				     AND @FromMarkActual = 0 AND @FromMarkWIAD = 0 )
				BEGIN
				  IF ( @pIsShiftleader = 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				  IF ( @pIsShiftleader = 1 AND ISNULL(@ToDuration,0) <> 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				END

				IF ( ( @FromAllocationsID <> @ToAllocationsID AND @ToIsFreelancer = 1 and ISNULL(@ToDutyTeamID,0) = 0 )
				     OR (@FromAllocationsID <> @ToAllocationsID AND @ToIsFreelancer = 0 and ISNULL(@ToDutyTeamID,0) = 0
					     AND ( @ToMarkWIAD = 1 OR @ToMarkActual = 1) ))
				BEGIN

				  SELECT @ToAllocationsID	 = AD_AllocationsID,
						 @ToAllocationsDutyID = AD_AllocationsDutyID,
						 @ToDutyType = AD_DutyType
				    FROM AllocationsDuties AD
				   WHERE AD_AllocationsID = @FromAllocationsID
				     AND AD_DutyType = 7
					 AND AD_DutyDate = @FromDutyDate

				END

				IF ( ISNULL(@ToStartTime,0) > 0 OR ISNULL(@ToEndTime,0) > 0)
				 BEGIN

					SELECT @IsDutyOverLap = IsDutyOverLap,
						   @prevOverlapMsg = PrevDayOvrlapMsg,
						   @nextOverlapMsg = NextDayOvrlapMsg
					  FROM ufn_check_DutyOverLap(@FromID,@ToStartTimeLocal,@ToEndTimeLocal)

					SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

					IF ( @IsDutyOverLap = 1 )
					 BEGIN
					  THROW 51010, @OvreLapMsg, 1;
					 END;
			     END

				IF ( ISNULL(@FromStartTime,0) > 0 OR ISNULL(@FromEndTime,0) > 0)
				 BEGIN

					SELECT @IsDutyOverLap = IsDutyOverLap,
						   @prevOverlapMsg = PrevDayOvrlapMsg,
						   @nextOverlapMsg = NextDayOvrlapMsg
					  FROM ufn_check_DutyOverLap(@ToID,@FromStartTimeLocal,@FromEndTimeLocal)

					SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

					IF ( @IsDutyOverLap = 1 )
					 BEGIN
					  THROW 51011, @OvreLapMsg, 1;
					 END;

				 END;

				IF NOT EXISTS (	SELECT 1
								  FROM ScheduledPersonTeam_LINK STL
								 WHERE STL.TeamID = @SchedulingTeamID
								   AND STL.ScheduledPersonID = @FromSPID
								   AND STL.scheduledType = 1
								   AND @FromDutyDate BETWEEN ISNULL(STL.StartDate, @FromDutyDate)
														AND ISNULL(STL.EndDate,@FromDutyDate)
                              )
				 BEGIN
				   THROW 51011, 'Scheduled Person Not assigned in this Team on this Day ', 1;
				 END;

				IF NOT EXISTS (	SELECT 1
								  FROM ScheduledPersonTeam_LINK STL
								 WHERE STL.TeamID = @SchedulingTeamID
								   AND STL.ScheduledPersonID = @ToSPID
								   AND STL.scheduledType = 1
								   AND @ToDutyDate BETWEEN ISNULL(STL.StartDate, @ToDutyDate)
														AND ISNULL(STL.EndDate,@ToDutyDate)
                              )
				 BEGIN
				   THROW 51012, 'Scheduled Person Not assigned in this Team on this Day ', 1;
				 END;

			    IF ( @pIsShiftleader = 0 AND ( ISNULL(@FromIsHomeTeam,1) = 1
					         AND (    ISNULL(@FromMarkWIAD,0) = 1
							       OR ISNULL(@FromMarkActual,0) = 1) ) )
				BEGIN
				  THROW 51014, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

			    IF ( @pIsShiftleader = 0 AND @FromIsFreelancer = 0
				     AND ( ISNULL(@FromIsHomeTeam,1) = 0
					   and ( ISNULL(@FromMarkWIAD,0) = 0 AND ISNULL(@FromMarkActual,0) = 0) ) )
				BEGIN
				  THROW 51015, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

                IF ( @FromLeaveType > 0 AND @FromLeaveStatus = 1 )
                BEGIN
				  THROW 51016, 'Cannot assign Absent/Sick/Leave Duty ', 1;
                END;

                IF ( @ToLeaveType > 0 AND @ToLeaveStatus = 1)
                BEGIN
				  THROW 51017, 'Cannot assign a Duty to Absent/Sick/Leave Days ', 1;
                END;

			    IF ( @pIsShiftleader = 0 AND ( ISNULL(@ToIsHomeTeam,1) = 1
					   and ( ISNULL(@ToMarkWIAD,0) = 1 or ISNULL(@ToMarkActual,0) = 1) ) )
				BEGIN
				  THROW 51018, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

			    IF ( @pIsShiftleader = 0 AND @ToIsFreelancer = 0
				     AND ( ISNULL(@ToIsHomeTeam,1) = 0
					   and ( ISNULL(@ToMarkWIAD,0) = 0 AND ISNULL(@ToMarkActual,0) = 0)) )
				BEGIN
				  THROW 51019, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

				SET @ScheduledpersonList = CAST(@FromSPID AS varchar)+','+CAST(@ToSPID AS varchar)
				SET @IsReturnData = 1

				    INSERT INTO @TempHistory
							(
								historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType
							)
					SELECT ht.id AS historytype,
						   @FromAllocationsDutyID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop (Swapped duty ['
						   + @FromDutyName +'] on '+ @FromDisplayName + ' to ['
						   + @ToDutyName +'] from ' + @ToDisplayName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationDuty'

				    INSERT INTO @TempHistory
							(
								historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType
							)
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
								THEN @FromAllocationsAPID
								ELSE @FromID END AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop (Swapped duty ['
						   + @FromDutyName +'] on '+ @FromDisplayName + ' to ['
						   + @ToDutyName +'] from ' + @ToDisplayName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

				IF ( @ToDutyType < 7 )
				  BEGIN
				    INSERT INTO @TempHistory
							(
								historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType
							)
						SELECT ht.id AS historytype,
							   @ToAllocationsDutyID AS attributeid,
							   'DH' AS HistorySubType,
							   getdate(),
							   @vuserID,
							   'Modified On '
							   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							   + ' By ' + @vname + ', Drag and Drop (Swapped duty ['
							   + @ToDutyName +'] on '+ @ToDisplayName + ' to ['
							   + @FromDutyName +'] from ' + @FromDisplayName +')',
							   'I'
						FROM HistoryTypes ht
					   WHERE ht.historytype = 'AllocationDuty'
					 END

				    INSERT INTO @TempHistory
							(
								historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType
							)
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
								THEN @ToAllocationsAPID
								ELSE @ToID END AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop (Swapped duty ['
						   + @ToDutyName +'] on '+ @ToDisplayName + ' to ['
						   + @FromDutyName +'] from ' + @FromDisplayName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

					INSERT INTO @TempHistory ( historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType )
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
								THEN @FromAllocationsAPID
								ELSE @FromID END AS attributeid,
							'PH' AS HistorySubType,
							getdate(),
							@vuserID,
							'Over12 was Removed by system On '+
							+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.',
							'I'
						FROM HistoryTypes ht
						WHERE @FromMarkOverTweleve > 0 and @FromMarkOverTweleve < 9
				          AND ht.historytype = CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

					INSERT INTO @TempHistory ( historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType )
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
								THEN @ToAllocationsAPID
								ELSE @ToID END AS attributeid,
							'PH' AS HistorySubType,
							getdate(),
							@vuserID,
							'Over12 was Removed by system On '+
							+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.',
							'I'
						FROM HistoryTypes ht
						WHERE @ToMarkOverTweleve > 0 and @ToMarkOverTweleve < 9
						  AND ht.historytype = CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END


				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = CASE WHEN ASP_AllocationsSPID = @FromID
													   THEN @ToAllocationsDutyID
													   ELSE @FromAllocationsDutyID END,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_WIADStatus = CASE WHEN ASP_AllocationsSPID = @FromID AND @FromIsFreelancer = 1
												 AND @ToDutyType NOT IN (7,9)
												THEN 2
												WHEN ASP_AllocationsSPID = @FromID AND @FromIsFreelancer = 1
												 AND @ToDutyType IN (7,9)
												THEN NULL
												WHEN ASP_AllocationsSPID = @ToID AND @ToIsFreelancer = 1
												THEN 2
												ELSE ASP_WIADStatus END,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = CASE WHEN ASP_AllocationsSPID = @FromID
												      THEN  case when @ToDuration > 43200
															and isnull(@ToIsOverrideOver12,1) = 1
															then 1 else 0 end
													  ELSE case when @FromDuration > 43200
															and isnull(@FromIsOverrideOver12,1) = 1
															then 1 else 0 end
													   END,
						  ASP_OverTwelveHrs = 0,
						  ASP_ChargingStatus = CASE WHEN @pIsShiftleader = 1 AND ASP_AllocationsSPID = @FromID
													THEN 0
													ELSE ASP_ChargingStatus END,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID IN ( @FromID, @ToID)

					UPDATE AllocationsDuties
					   SET AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID IN ( @FromAllocationsDutyID,@ToAllocationsDutyID);


					INSERT INTO History ( historytype,
								attributeid,
								HistorySubType,
								[datetime],
								userid,
								history )
					SELECT historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history
						FROM @TempHistory


				   IF(@pIsShiftleader = 1)
				     BEGIN
						DELETE FROM ChargingDutyMapping_Link WHERE AllocationId = @FromID
				     END

					SET @ToDurationDutySummary = CASE WHEN @FromMarkWIAD = 1
										   THEN 0
										   ELSE ISNULL(@ToDuration,0) - ISNULL(@ToBreakDuration,0) END
					SET @FromDurationDutySummary = CASE WHEN @FromMarkWIAD = 1
											 THEN 0
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) END

					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,@FromDurationDutySummary,
										@ToDurationDutySummary,@FromOverTimeHrs,@ToOverTimeHrs,@pNetLogin

					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

					SET @ToDurationDutySummary = CASE WHEN @ToMarkWIAD = 1
										   THEN 0
										   ELSE ISNULL(@ToDuration,0) - ISNULL(@ToBreakDuration,0) END
					SET @FromDurationDutySummary = CASE WHEN @ToMarkWIAD = 1
											 THEN 0
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) END

					DELETE @EditAllocationStatus;
					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @ToSPID,
										@ToDurationDutySummary,@FromDurationDutySummary,
										@ToOverTimeHrs,@FromOverTimeHrs,@pNetLogin
					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

				   EXEC	@ReturnValue = usp_UpdateWTD @FromID, @pNetLogin
				   EXEC	@ReturnValue = usp_UpdateWTD @ToID, @pNetLogin


					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,
													 @FromAllocationsDutyID,
													 @FromID,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,
													@ToAllocationsDutyID,
													 @ToID,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51001, 'Error while creating Duty update status', 1;
					END;

		   END

		------------------------------------------------
		-- Assign Duties from Unallocted to Allocted
		------------------------------------------------

           IF  (@EditType = 'ASSIGN')
		   BEGIN

			    IF (@FromID IS NULL OR ( @ToID IS NULL AND @pSchedulingPersonID IS NULL) )
				BEGIN

				  THROW 51020, 'From ID and To ID Cannot be NULL', 1;

				END

				 SELECT @FromIDay        = AD_iDay,
				        @FromIsHomeTeam  = 1,
						@FromMarkWIAD    = 0,
						@FromMarkActual  = 0,
						@FromDutyName    = AD_DutyName,
						@FromDutyDate    = AD_DutyDate,
						@FromDuration	 = AD_Duration,
						@FromStartTime   = AD_StartTimeSec,
						@FromEndTime     = AD_EndTimeSec,
						@vWeekNumber     = AL_WeekNumber,
						@FromDutystatus	 = AD_DutyStatus,
						@FromStartTimeLocal	= AD_DutyStartTimeLocal,
						@FromEndTimeLocal	= AD_DutyEndTimeLocal,
						@AllocationsID		= AL_AllocationsID,
						@SchedulingTeamID   = AL_SchedulingTeamID,
						@FromIsOverrideOver12 = AD_IsOverrideOver12,
						@FromAllocationsDutyID = AD_AllocationsDutyID,
						@FromOverTimeHrs	   = 0,
						@FromBreakDuration	   = AD_DutyBreakTime
				   FROM Allocations AL
				  INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
				  WHERE AD_AllocationsDutyID = @FromID

				IF ( ISNULL(@FromDutystatus,0) <> 0 )
				 BEGIN
				   THROW 51021, 'There is problem with the data. Underlying data has been modified. Please refresh the page and try again.', 1;
				 END;

				IF ( ISNULL(@ToID,0) = 0 AND @pSchedulingPersonID > 0 )
				 BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @AllocationsID,
													0,
													@pSchedulingPersonID,
													@FromDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					END
					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

					SET @ToID = @AllocationsSPID;

				 END;

				 SELECT @ToIDay              = ASP_iDay,
				        @ToIsHomeTeam        = CASE WHEN AL_AllocationsID <> ASP_AllocationsID
													 THEN 0
													 ELSE 1 END,
						@ToMarkWIAD          = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@ToMarkActual        = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@ToDutyName          = AD_DutyName,
						@ToDutyTeamID        = ASP_DutyTeamID,
						@ToDutyDate          = ASP_DutyDate,
						@ToSPID              = ASP_SchedulingPersonID,
						@ToDuration          = AD_Duration,
						@ToMarkedOvertime    = ASP_MarkedOverTime,
						@ToDisplayName       = UD_DisplayName,
						@ToStarttime         = AD_StartTimeSec,
						@ToEndTime           = AD_EndTimeSec,
						@vWeekNumber         = AL_WeekNumber,
						@ToLeaveStartTime    = ASP_LeaveStartTimeSec,
						@ToLeaveEndTime      = ASP_LeaveEndTimeSec,
						@ToLeaveType         = ASP_LeaveType,
						@ToLeaveStatus       = ASP_LeaveStatus,
						@ToStartTimeLocal	 = AD_DutyStartTimeLocal,
						@ToEndTimeLocal	     = AD_DutyEndTimeLocal,
						@ToChargingStatus    = ASP_ChargingStatus,
						@ToDutyType          = AD_DutyType,
						@ToAllocationsID	 = ASP_AllocationsID,
						@ToAllocationsDutyID = AD_AllocationsDutyID,
						@ToMarkOverTweleve   = ASP_OverTwelveStatus,
						@ToIsOverrideOver12	 = AD_IsOverrideOver12,
						@ToOverTimeHrs		 = ASP_OverTimeHours,
						@ToBreakDuration	 = AD_DutyBreakTime
				   FROM Allocations AL
				  INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @ToID;

				IF ( @AllocationsID <> @ToAllocationsID )
				  BEGIN

				     SELECT @ToAllocationsAPID = AP.AAP_AllocationsAPID
					  FROM AllocationsAddPersons AP
					 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_SchedulingPersonID = AP.AAP_SchedulingPersonID
															   AND ASP.ASP_DutyDate = AP.AAP_DutyDate
					  WHERE ASP.ASP_AllocationsID = @ToAllocationsID
					    AND ASP.ASP_AllocationsSPID = @ToID
						AND AP.AAP_AllocationsID = @AllocationsID

					 EXEC @ReturnValue = usp_Create_HistoryAddPerson @ToAllocationsAPID

				  END

				IF ( @pIsShiftleader <> 1 AND ISNULL(@ToChargingStatus,0) <> 0)
				BEGIN
				  THROW 51005, 'This duty cannot be deleted/swapped as it has one or more Charging records associated with it.
						Please remove the Charging records before trying to delete', 1;
				END

                IF ( @ToDutyType IN (8,11,12) )
			  	 BEGIN
				   SET @Message = 'Cannot SWAP Duty ['+@FromDutyName+'] with other duty'
				 END;

                IF ( @ToDutyType IN (8,11,12) )
			  	 BEGIN
				   THROW 51006, @Message, 1;
				 END;

                IF ( @ToLeaveType = 6 AND ISNULL(@ToLeaveStatus,0) <> 9 )
				 BEGIN
				   THROW 51007, 'You cannot swap/assign duty containing part day leave', 1;
				 END;

                IF ( ISNULL(@ToMarkedOvertime,0) > 0 )
				 BEGIN
				   THROW 51008, 'You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty  ', 1;
				 END;

				IF ( @Fromiday <> @Toiday )
				 BEGIN
				   THROW 51013, 'Only same Day duty can be swapped ', 1;
				 END;

				SET @ToIsFreelancer = dbo.ufn_isfreelancer(@ToSPID,@FromDutyDate)

			    IF ( ISNULL(@ToDutyTeamID,0) <> 0 AND ISNULL(@ToDutyTeamID,0) <>  @SchedulingTeamID AND @ToDutyType NOT IN (7,9) )
				BEGIN
				  IF ( @pIsShiftleader = 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				  IF ( @pIsShiftleader = 1 AND ISNULL(@ToDuration,0) <> 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				END

				IF ( @FromAllocationsID <> @ToAllocationsID AND @ToIsFreelancer = 0 )
				BEGIN
				  IF ( @pIsShiftleader = 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				  IF ( @pIsShiftleader = 1 AND ISNULL(@ToDuration,0) <> 0 )
                   BEGIN
				     THROW 51006, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				END

				IF ( ( @FromAllocationsID <> @ToAllocationsID AND @ToIsFreelancer = 1 and ISNULL(@ToDutyTeamID,0) = 0 )
				     OR (@FromAllocationsID <> @ToAllocationsID AND @ToIsFreelancer = 0 and ISNULL(@ToDutyTeamID,0) = 0
					     AND ( @ToMarkWIAD = 1 OR @ToMarkActual = 1) ))
				BEGIN

				  SELECT @ToAllocationsID	 = AD_AllocationsID,
						 @ToAllocationsDutyID = AD_AllocationsDutyID
				    FROM AllocationsDuties AD
				   WHERE AD_AllocationsID = @FromAllocationsID
				     AND AD_DutyType = 7
					 AND AD_DutyDate = @FromDutyDate

					 SET @ToDutyType = 7

				END

				IF ( ISNULL(@FromStartTime,0) > 0 OR ISNULL(@FromEndTime,0) > 0)
				 BEGIN

					SELECT @IsDutyOverLap = IsDutyOverLap,
						   @prevOverlapMsg = PrevDayOvrlapMsg,
						   @nextOverlapMsg = NextDayOvrlapMsg
					  FROM ufn_check_DutyOverLap(@ToID,@FromStartTimeLocal,@FromEndTimeLocal)

					SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

					IF ( @IsDutyOverLap = 1 )
					 BEGIN
					  THROW 51010, @OvreLapMsg, 1;
					 END;

				 END;

				IF NOT EXISTS (	SELECT 1
								  FROM ScheduledPersonTeam_LINK STL
								 WHERE STL.TeamID = @SchedulingTeamID
								   AND STL.ScheduledPersonID = @ToSPID
								   AND STL.scheduledType = 1
								   AND @ToDutyDate BETWEEN ISNULL(STL.StartDate, @ToDutyDate)
														AND ISNULL(STL.EndDate,@ToDutyDate)
                              )
				 BEGIN
				   THROW 51012, 'Scheduled Person Not assigned in this Team on this Day ', 1;
				 END;

                IF ( @ToLeaveType > 0 AND @ToLeaveStatus = 1)
                BEGIN
				  THROW 51017, 'Cannot assign a Duty to Absent/Sick/Leave Days ', 1;
                END;

			    IF ( @pIsShiftleader = 0 AND ( @AllocationsID = @ToAllocationsID
					   and ( ISNULL(@ToMarkWIAD,0) = 1 or ISNULL(@ToMarkActual,0) = 1) ) )
				BEGIN
				  THROW 51018, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

			    IF ( @pIsShiftleader = 0 AND @ToIsFreelancer = 0
				     AND ( @AllocationsID <> @ToAllocationsID
					   and ( ISNULL(@ToMarkWIAD,0) = 0 AND ISNULL(@ToMarkActual,0) = 0)) )
				BEGIN
				  THROW 51019, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
					SELECT ht.id AS historytype,
						   @FromAllocationsDutyID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Allocated from UnAllocated Duties to '
						   + @ToDisplayName +' (Swapped '+ @FromDutyName + ' to '
						   + @ToDutyName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationDuty'

				  IF ( @ToDutyType < 7 )
				   BEGIN
				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
						SELECT ht.id AS historytype,
							   @ToAllocationsDutyID AS attributeid,
							   'DH' AS HistorySubType,
							   getdate(),
							   @vuserID,
							   'Modified On '
							   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							   + ' By ' + @vname
							   + ', Drag and Drop from Allocated Duties ( Swapped '
							   + @ToDutyName +' to '+ @FromDutyName + ' from '
							   + @FromDisplayName +')',
							   'I'
						FROM HistoryTypes ht
					   WHERE ht.historytype = 'AllocationDuty'
					 END

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
								THEN @ToAllocationsAPID
								ELSE @ToID END AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Allocated from UnAllocated Duties to '
						   + @ToDisplayName +' (Swapped '+ @FromDutyName + ' to '
						   + @ToDutyName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

					INSERT INTO @TempHistory ( historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType )
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
								THEN @ToAllocationsAPID
								ELSE @ToID END AS attributeid,
							'PH' AS HistorySubType,
							getdate(),
							@vuserID,
							'Over12 was Removed by system On '+
							+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.',
							'I'
						FROM HistoryTypes ht
						WHERE @ToMarkOverTweleve > 0 and @ToMarkOverTweleve < 9
						  AND ht.historytype = CASE WHEN ISNULL(@ToAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = @FromAllocationsDutyID,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_WIADStatus = CASE WHEN @ToIsFreelancer = 1 THEN 2
												ELSE ASP_WIADStatus END,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = case when @FromDuration > 43200
						                           and isnull(@FromIsOverrideOver12,1) = 1
						                          then 1 else 0 end,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_ChargingStatus = CASE WHEN @pIsShiftleader = 1
													THEN 0
													ELSE ASP_ChargingStatus END,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID = @ToID

					UPDATE AllocationsDuties
					   SET AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_DutyStatus = 1,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID = @FromAllocationsDutyID;

				 IF ( @ToDutyType NOT IN (7,8 ) )
				  BEGIN
					UPDATE AllocationsDuties
					   SET AD_DutyStatus = CASE WHEN ISNULL(@ToDuration,0) = 0
												THEN 9
												WHEN ISNULL(@ToDuration,0) > 0 AND ISNULL(@ToStarttime,0) = 0 
												 AND ISNULL(@ToEndtime,0) = 0
												THEN 9
												WHEN ISNULL(AD_IsNeedCovering,1) = 0
												THEN 9
												WHEN ISNULL(@ToDuration,0) > 0
												THEN 0
											END,
					       AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID = @ToAllocationsDutyID;
				  END

				IF ( @ToDutyType = 9 )
				 BEGIN

				   SELECT @HistoryType = id
				   FROM HistoryTypes
				  WHERE historytype = 'AllocationDuty'

				   UPDATE History
				      SET AttributeID = @FromAllocationsDutyID
					WHERE AttributeID = @ToAllocationsDutyID
					  AND HistoryType = @HistoryType

				 END

					INSERT INTO History ( historytype,
								attributeid,
								HistorySubType,
								[datetime],
								userid,
								history )
					SELECT historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history
						FROM @TempHistory


				   IF(@pIsShiftleader = 1)
				    BEGIN
					 DELETE FROM ChargingDutyMapping_Link WHERE AllocationId = @ToID
				    END

					SET @ToDuration = CASE WHEN @ToMarkWIAD = 1
										   THEN 0
										   ELSE ISNULL(@ToDuration,0) - ISNULL(@ToBreakDuration,0) END
					SET @FromDuration = CASE WHEN @ToMarkWIAD = 1
											 THEN 0
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) END

					DELETE @EditAllocationStatus;
					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @ToSPID,@ToDuration,
										@FromDuration,@ToOverTimeHrs,@FromOverTimeHrs,@pNetLogin

					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

				    EXEC	@ReturnValue = usp_UpdateWTD @ToID, @pNetLogin


					EXEC @ReturnValue = usp_CreateAllocationsUpdate NULL,@ToAllocationsDutyID,
													 NULL,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000,'Error while creating Duty update status', 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate NULL, @FromID,
													 @ToID,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

		   END

		------------------------------------------------
		-- Unassign Duties from allocated to Unallocated
		------------------------------------------------

           IF  (@EditType = 'UNASSIGN')
 		    BEGIN

			    IF (@FromID IS NULL )
				 BEGIN
				  THROW 51000, 'From ID Cannot be NULL', 1;
				 END

				 SELECT  @AllocationsID			 = AL_AllocationsID,
						 @SchedulingTeamID       = AL_SchedulingTeamID,
						 @vWeekNumber            = AL_WeekNumber
					FROM Allocations AL
				   WHERE AL_AllocationsID = @pAllocationsID;

				 SELECT @FromIDay               = ASP_iDay,
				        @FromIsHomeTeam         = CASE WHEN ASP_AllocationsID <> @AllocationsID
												  	   THEN 0
													   ELSE 1 END,
						@FromMarkWIAD           = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@FromMarkActual         = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@FromDutyName           = AD_DutyName,
						@FromDutyTeamID         = ASP_DutyTeamID,
						@FromDutyDate           = ASP_DutyDate,
						@FromSPID               = ASP_SchedulingPersonID,
						@FromDuration           = AD_Duration,
						@FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromDisplayName        = UD_DisplayName,
						@FromStarttime          = AD_StartTimeSec,
						@FromEndTime            = AD_EndTimeSec,
						@FromLeaveStartTime     = ASP_LeaveStartTimeSec,
						@FromLeaveEndTime       = ASP_LeaveEndTimeSec,
						@FromLeaveType          = ASP_LeaveType,
						@FromLeaveStatus        = ASP_LeaveStatus,
						@FromStartTimeLocal	    = AD_DutyStartTimeLocal,
						@FromEndTimeLocal	    = AD_DutyEndTimeLocal,
						@FromChargingStatus     = ASP_ChargingStatus,
						@FromDutyType           = AD_DutyType,
						@FromAllocationsID      = ASP_AllocationsID,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus,
						@FromIsOverrideOver12   = AD_IsOverrideOver12,
						@FromOverTimeHrs		= ASP_OverTimeHours,
						@FromBreakDuration		= AD_DutyBreakTime
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @FromID;

				IF ( @AllocationsID <> @FromAllocationsID )
				  BEGIN

				     SELECT @FromAllocationsAPID = AP.AAP_AllocationsAPID
					  FROM AllocationsAddPersons AP
					 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_SchedulingPersonID = AP.AAP_SchedulingPersonID
															   AND ASP.ASP_DutyDate = AP.AAP_DutyDate
					  WHERE ASP.ASP_AllocationsID = @FromAllocationsID
					    AND ASP.ASP_AllocationsSPID = @FromID
						AND AP.AAP_AllocationsID = @AllocationsID
				  END

				/*IF ( @pIsShiftleader <> 1 AND ISNULL(@FromChargingStatus,0) <> 0 )
				 BEGIN
					THROW 51000, 'This duty cannot be deleted/swapped as it has one or more Charging records associated with it.
					Please remove the Charging records before trying to delete', 1;
				 END*/

                IF ( @FromDutyType IN (8,11,12) )
			  	 BEGIN
				   SET @Message = 'Cannot SWAP Duty ['+@FromDutyName+'] with other duty'
				 END;

                IF ( @FromDutyType IN (8,11,12) )
			  	 BEGIN
				   THROW 51002, @Message, 1;
				 END;

                IF ( @FromDutyType IN (7,9) )
				 BEGIN
				   THROW 51002, 'There is problem with the data. Underlying data has been modified. Please refresh the page and try again.', 1;
				 END;

                IF ( @FromLeaveType = 6 AND ISNULL(@FromLeaveStatus,0) <> 9 )
				 BEGIN
				   THROW 51003, 'You cannot swap/assign duty containing part day leave', 1;
				 END;

                IF ( ISNULL(@FromMarkedOvertime,0) > 0 )
				 BEGIN
				   THROW 51004, 'You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty  ', 1;
				 END;

			    IF ( ISNULL(@FromDutyTeamID,0) <> 0 AND ( ISNULL(@FromDutyTeamID,0) <>  @SchedulingTeamID )  )
				 BEGIN
				  IF ( @pIsShiftleader = 0 )
                   BEGIN
				     THROW 51005, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				  IF ( @pIsShiftleader = 1 AND ISNULL(@FromDuration,0) <> 0 )
                   BEGIN
				     THROW 51005, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END;
				 END

				SET @FromIsFreelancer = dbo.ufn_isfreelancer(@FromSPID,@FromDutyDate)

			    IF ( @pIsShiftleader = 0 AND ( ISNULL(@FromIsHomeTeam,1) = 1
					         AND (    ISNULL(@FromMarkWIAD,0) = 1
							       OR ISNULL(@FromMarkActual,0) = 1) ) )
				BEGIN
				  THROW 51014, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

			    IF ( @pIsShiftleader = 0 AND @FromIsFreelancer = 0
				     AND ( ISNULL(@FromIsHomeTeam,1) = 0
					   and ( ISNULL(@FromMarkWIAD,0) = 0 AND ISNULL(@FromMarkActual,0) = 0) ) )
				BEGIN
				  THROW 51015, 'It is not possible to swap a duty with one belonging to another team ', 1;
				END;

                IF ( @FromLeaveType > 0 AND @FromLeaveStatus = 1 )
                BEGIN
				  THROW 51016, 'Cannot assign Absent/Sick/Leave Duty ', 1;
                END;

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
					SELECT ht.id AS historytype,
						   @FromAllocationsDutyID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', UnAllocated Duty ('
						   + @FromDutyName +') from ' + @FromDisplayName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationDuty'

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
								THEN @FromAllocationsAPID
								ELSE @FromID END AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', UnAllocated Duty ('
						   + @FromDutyName +') from ' + @FromDisplayName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

					INSERT INTO @TempHistory ( historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType )
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
								THEN @FromAllocationsAPID
								ELSE @FromID END AS attributeid,
							'PH' AS HistorySubType,
							getdate(),
							@vuserID,
							'Over12 was Removed by system '+
							+ ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.',
							'I'
						FROM HistoryTypes ht
						WHERE @FromMarkOverTweleve > 0
						  and @FromMarkOverTweleve < 9
						  and ht.historytype = CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

				   IF ( @FromAllocationsID <> @pAllocationsID )
				    BEGIN
					 SET @AllocationsID = @FromAllocationsID
					END

				  SELECT @SchedulingTeamID = AL_SchedulingTeamID,
						 @AllocationsDutyID = AD_AllocationsDutyID
					FROM Allocations AL
				   INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
				   WHERE AL.AL_AllocationsID = @AllocationsID
				     AND AD_DutyDate = @FromDutyDate
					 AND AD_DutyType = 7

				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = @AllocationsDutyID,
						  ASP_DutyTeamID = CASE WHEN ASP_WIADStatus IN (1,2)
												THEN NULL
												ELSE @SchedulingTeamID END,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = 0,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_WIADStatus = CASE WHEN ISNULL(@FromIsFreelancer,0) = 1
												THEN NULL
												ELSE ASP_WIADStatus END,
						  ASP_ChargingStatus = CASE WHEN @pIsShiftleader = 1
													THEN 0
													ELSE ASP_ChargingStatus END,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID =  @FromID

					UPDATE AllocationsDuties
					   SET AD_DutyStatus = CASE WHEN ISNULL(@FromDuration,0) = 0
												THEN 9
												WHEN ISNULL(AD_IsNeedCovering,1) = 0
												THEN 9
												WHEN ISNULL(@FromDuration,0) > 0
												THEN 0
											END,
						   AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID = @FromAllocationsDutyID;

					INSERT INTO History ( historytype,
								attributeid,
								HistorySubType,
								[datetime],
								userid,
								history )
					SELECT historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history
						FROM @TempHistory

				   IF(@pIsShiftleader = 1)
				    BEGIN
					 DELETE FROM ChargingDutyMapping_Link WHERE AllocationId = @FromID
				    END

					SET @FromDuration = CASE WHEN @FromMarkWIAD = 1
											 THEN 0
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) END

					DELETE @EditAllocationStatus;
					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,@FromDuration,
										0,@FromOverTimeHrs,0,@pNetLogin

					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,
													@AllocationsDutyID,
													 @FromID,
													 @PublishStatus,
													 @pNetLogin;


					IF ( ISNULL(@ReturnValue,0) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,
													 @FromAllocationsDutyID,
													 0,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

			   EXEC	@ReturnValue = usp_UpdateWTD @FromID, @pNetLogin

		    END


	    -----------------------------------------------
		-- Add Misc. Duty
		-----------------------------------------------

		  IF ( @EditType = 'ASSIGNMISCDUTY')
		    BEGIN

			    IF ((@FromID IS NULL AND @pSchedulingPersonID IS NULL) OR
					( @pMasterDutyID IS NULL OR @pSchedulingPersonID IS NULL OR @pDutyDate IS NULL))
				BEGIN
				  THROW 51010, 'From ID and MasterDuty ID Cannot be NULL', 1;
				END

				 SELECT  @AllocationsID			 = AL_AllocationsID,
						 @SchedulingTeamID       = AL_SchedulingTeamID,
						 @vWeekNumber            = AL_WeekNumber
					FROM Allocations AL
				   WHERE AL_AllocationsID = @pAllocationsID;

				IF ( ISNULL(@FromID,0) = 0 AND @pSchedulingPersonID > 0  AND @pDutyDate IS NOT NULL )
				 BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @AllocationsID,
													0,
													@pSchedulingPersonID,
													@pDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;


					IF ( ISNULL(@ReturnValue,0) > 0 )
					 BEGIN
					   THROW 51000, 'Error while creating scheduled person allocation', 1;
					 END
					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

					SET @FromID = @AllocationsSPID;

				 END;

				 SELECT @FromIDay               = ASP_iDay,
				        @FromIsHomeTeam         = CASE WHEN ASP_AllocationsID <> @AllocationsID
												  	   THEN 0
													   ELSE 1 END,
						@FromMarkWIAD           = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@FromMarkActual         = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@FromDutyName           = AD_DutyName,
						@FromDutyTeamID         = ASP_DutyTeamID,
						@FromDutyDate           = ASP_DutyDate,
						@FromSPID               = ASP_SchedulingPersonID,
						@FromDuration           = AD_Duration,
						@FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromDisplayName        = UD_DisplayName,
						@FromStarttime          = AD_StartTimeSec,
						@FromEndTime            = AD_EndTimeSec,
						@FromLeaveStartTime     = ASP_LeaveStartTimeSec,
						@FromLeaveEndTime       = ASP_LeaveEndTimeSec,
						@FromLeaveType          = ASP_LeaveType,
						@FromLeaveStatus        = ASP_LeaveStatus,
						@FromStartTimeLocal	    = AD_DutyStartTimeLocal,
						@FromEndTimeLocal	    = AD_DutyEndTimeLocal,
						@FromChargingStatus     = ASP_ChargingStatus,
						@FromDutyType           = AD_DutyType,
						@FromAllocationsID      = ASP_AllocationsID,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus,
						@FromIsOverrideOver12	= AD_IsOverrideOver12,
						@FromBreakDuration		= AD_DutyBreakTime,
						@FromOverTimeHrs		= ASP_OverTimeHours
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @FromID;

				IF ( @AllocationsID <> @FromAllocationsID )
				  BEGIN

				     SELECT @FromAllocationsAPID = AP.AAP_AllocationsAPID
					  FROM AllocationsAddPersons AP
					 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_SchedulingPersonID = AP.AAP_SchedulingPersonID
															   AND ASP.ASP_DutyDate = AP.AAP_DutyDate
					  WHERE ASP.ASP_AllocationsID = @FromAllocationsID
					    AND ASP.ASP_AllocationsSPID = @FromID
						AND AP.AAP_AllocationsID = @AllocationsID

					 EXEC @ReturnValue = usp_Create_HistoryAddPerson @FromAllocationsAPID

				  END


				IF ( @pIsShiftleader <> 1 AND ISNULL(@FromChargingStatus,0) <> 0)
				BEGIN
				  THROW 51005, 'This duty cannot be deleted/swapped as it has one or more Charging records associated with it.
						Please remove the Charging records before trying to delete', 1;
				END

                 IF ( @FromLeaveType = 6 AND ISNULL(@FromLeaveStatus,0) <> 9 )
				  BEGIN
				   THROW 51003, 'You cannot swap/assign duty containing part day leave', 1;
				  END;

                IF NOT EXISTS ( SELECT 1 FROM MasterDuties (NOLOCK) where MasterDutyID = @pmasterdutyid )
				 BEGIN
				   THROW 51011, 'Misc Duty Does nots exists', 1;
				 END;

				IF NOT EXISTS (	SELECT 1
								  FROM ScheduledPersonTeam_LINK STL (NOLOCK)
								 WHERE STL.TeamID = @SchedulingTeamID
								   AND STL.ScheduledPersonID = @pSchedulingPersonID
								   AND STL.scheduledType = 1
								   AND @FromDutyDate BETWEEN STL.StartDate AND STL.EndDate
                              )
				 BEGIN
				   THROW 51011, 'Scheduled Person Not assigned in this Team on this Day ', 1;
				 END;

			    IF ( @pIsShiftleader = 0 AND (IsNull(@FromIsHomeTeam,1) = 1
					   and ( IsNull(@FromMarkWIAD,0) = 1 or isNull(@FromMarkActual,0) = 1) ) )
				BEGIN
				  THROW 51012, 'This Scheduled Person is assigned to a different team on this day', 1;
				END

                IF ( @FromLeaveType > 0 AND @FromLeaveStatus = 1 )
                BEGIN
				  THROW 51016, 'Cannot assign Absent/Sick/Leave Duty ', 1;
                END;

 			    SET @IsFreelancer = dbo.ufn_IsFreeLancer(@pSchedulingPersonID,@pDutyDate)

			    IF ( @pIsShiftleader = 0 AND @IsFreelancer = 0
				       AND ( IsNull(@FromIsHomeTeam,1) = 0
					   and ( IsNull(@FromMarkWIAD,0) = 0 AND isNull(@FromMarkActual,0) = 0) ) )
				BEGIN
				  THROW 51014, 'This Scheduled Person is assigned to a different team on this day ', 1;
				END

			    IF ( IsNull(@FromDutyTeamID,0) <> 0 AND ( IsNull(@FromDutyTeamID,0) <>  @SchedulingTeamID )  )
				BEGIN
				  THROW 51015, 'Duty Assigned From a different team on this day ', 1;
				END

				IF ( ISNULL(@FromMarkedOvertime,0) > 0 )
				 BEGIN
				   THROW 51004, 'You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty  ', 1;
				 END;

				  INSERT INTO AllocationsDuties
						(
						AD_AllocationsID,
						AD_DutyName,
						AD_Duration,
						AD_PlannedDuration,
						AD_iDay,
						AD_StartTimeSec,
						AD_EndTimeSec,
						AD_Comments,
						AD_DutyDate,
						AD_DutyStartTimeUTC,
						AD_DutyEndTimeUTC,
						AD_DutyStartTimeLocal,
						AD_DutyEndTimeLocal,
						AD_DutyBreakTime,
						AD_MasterDutyID,
						AD_DutyType,
						AD_DutyStatus,
						AD_DutyColourID,
						AD_isAttention,
						AD_isRequest,
						AD_PlannedDutyBreakTime,
						AD_IsNeedCovering,
						AD_IsOverrideOver12,
						AD_IsDutyEdited,
						AD_DutyProgramID1,
						AD_DutyProgramID2,
						AD_DutyProgramID3,
						AD_DutyProgramID4,
						AD_DutyProgramID5,
						AD_DutyProgramID6,
						AD_IsEditedDutyAttention,
						AD_CreatedBy,
						AD_CreatedDate
						 )
				  SELECT @AllocationsID AS allocationid,
						 MD.dutyname,
						 case when isnull(MD.duration,0) = 0 then
						 case when MD.endtime > MD.starttime then MD.endtime- MD.starttime
							  when MD.endtime < MD.starttime then (86400-MD.starttime)+MD.endtime end
							  else  MD.duration end as duration,
						 case when isnull(MD.duration,0) = 0 then
						 case when MD.endtime > MD.starttime then MD.endtime- MD.starttime
							  when MD.endtime < MD.starttime then (86400-MD.starttime)+MD.endtime end
							  else  MD.duration end AS PlannedDuration,
					   td.ixdayinweek,
					   case when MD.StartTime >= 86400 then ( MD.StartTime - 86400) else MD.StartTime end AS starttime,
					   case when MD.EndTime > 86400 then (MD.EndTime - 86400) else MD.EndTime end AS endtime,
					   MD.DutyComment,
					   td.dDateTime,
					   CASE
						 WHEN md.starttime IS  NULL THEN NULL
						 WHEN md.starttime = 0 AND md.EndTime > 0 THEN td.dDateTime
						 WHEN md.starttime IS NOT NULL THEN dbo.ufn_ConvertToDateTime( td.dDateTime,md.starttime)
						 ELSE td.dDateTime END AS StartDateUTC,
					    CASE
						 WHEN md.endtime IS  NULL THEN NULL
						 WHEN MD.StartTime > 0 AND md.EndTime = 0 THEN DATEADD(DAY,1,td.dDateTime)
						 WHEN md.endtime IS NOT NULL
						 THEN  CASE WHEN MD.EndTime = 86400
									THEN DATEADD(DAY,1,td.dDateTime)
									WHEN MD.EndTime > 86400
									THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime), md.Endtime - 86400)
									WHEN MD.StartTime > MD.EndTime
									THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime), md.Endtime)
									ELSE td.dDateTime End
							    END AS EndDateUTC,
					    CASE
						 WHEN md.starttime IS  NULL THEN NULL
						 WHEN md.starttime = 0 AND md.EndTime > 0 THEN td.dDateTime
						 WHEN md.starttime IS NOT NULL THEN dbo.ufn_ConvertToDateTime( td.dDateTime,md.starttime)
						 ELSE td.dDateTime END AS StartDateLocal,
					    CASE
						 WHEN md.endtime IS  NULL THEN NULL
						 WHEN MD.StartTime > 0 AND md.EndTime = 0 THEN DATEADD(DAY,1,td.dDateTime)
						 WHEN md.endtime IS NOT NULL
						 THEN  CASE WHEN MD.EndTime = 86400
									THEN DATEADD(DAY,1,td.dDateTime)
									WHEN MD.EndTime > 86400
									THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime), md.Endtime - 86400)
									WHEN MD.StartTime > MD.EndTime
									THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,td.dDateTime), md.Endtime)
									ELSE td.dDateTime End
							    END AS EndDateLocal,
						 MD.BreakTime,
						 MD.MasterDutyID,
						 MD.DutyTypeID as DutyTypeID,
						 1 as DutyStatus,
						 MD.dutycolourid,
						 0,
						 0,
						 MD.BreakTime as PlannedDutyBreakTime,
						 MD.IsNeedCovering,
						 MD.IsOverrideOver12,
						 0,
						 md.DutyProgramId1,
						 md.DutyProgramId2,
						 md.DutyProgramId3,
						 md.DutyProgramId4,
						 md.DutyProgramId5,
						 md.DutyProgramId6,
						 CASE WHEN ISNULL(@pIsShiftleader,0) = 1 THEN 1 ELSE 0 END,
						 @vuserID,
						 getutcdate()
					FROM MasterDuties MD
				   INNER JOIN TimeDimension TD ON 1 =1
				   WHERE TD.dDateTime = @FromDutyDate
				     AND MD.MasterDutyID = @pMasterDutyID

			    SET @AllocationsDutyID = @@IDENTITY

				  SELECT @ToDuration = ISNULL(MD.Duration,0),
				         @ToIsOverrideOver12 = ISNULL(MD.IsOverrideOver12,0),
						 @ToDutyName = DutyName ,
						 @ToBreakDuration = BreakTime
				    FROM MasterDuties MD
				   WHERE MD.MasterDutyID = @pMasterDutyID

				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = @AllocationsDutyID,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_WIADStatus = CASE WHEN @IsFreelancer = 1 THEN 2
												ELSE ASP_WIADStatus END,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = case when @ToDuration > 43200
						                           and isnull(@ToIsOverrideOver12,1) = 1
						                          then 1 else 0 end,
						  ASP_ChargingStatus = CASE WHEN @pIsShiftleader = 1
													THEN 0
													ELSE ASP_ChargingStatus END,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID = @FromID

				 IF ( @FromDutyType NOT IN (7,8) )
				  BEGIN
					UPDATE AllocationsDuties
					   SET AD_DutyStatus = CASE WHEN ISNULL(AD_Duration,0) = 0
												THEN 9
												WHEN ISNULL(AD_Duration,0) > 0
												THEN 0
											END,
					       AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID = @FromAllocationsDutyID;
				  END

				  IF ( @FromDutyType < 7 )
				   BEGIN
				    INSERT INTO @TempHistory
							( historytype,
							  attributeid,
							  HistorySubType,
							  CreateDateTime,
							  userid,
							  history
							)
					SELECT ht.id AS historytype,
						   @FromAllocationsDutyID AS attributeid,
						   'DH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT (Getdate(), 'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop from Misc. Duties (Swapped '
						   + @FromDutyName+' to '+ @ToDutyName+ ' ) to '
						   +@FromDisplayName+'.'
					FROM  HistoryTypes (NOLOCK) HT
					WHERE  historytype = 'AllocationDuty'

				   END

				    INSERT INTO @TempHistory
							( historytype,
							  attributeid,
							  HistorySubType,
							  CreateDateTime,
							  userid,
							  history
							)
					SELECT ht.id AS historytype,
						   @AllocationsDutyID AS attributeid,
						   'DH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT (Getdate(), 'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop from Misc. Duties (Swapped '
						   + @FromDutyName+' to '+ @ToDutyName+ ' ) to '
						   +@FromDisplayName+'.'
					FROM   HistoryTypes (NOLOCK) HT
					WHERE  historytype = 'AllocationDuty'


				    INSERT INTO @TempHistory
							( historytype,
							  attributeid,
							  HistorySubType,
							  CreateDateTime,
							  userid,
							  history
							)
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
								THEN @FromAllocationsAPID
								ELSE @FromID END AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT (Getdate(), 'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop from Misc. Duties (Swapped '
						   + @FromDutyName+' to '+ @ToDutyName + ' ) to '
						   +@FromDisplayName+'.'
					FROM   HistoryTypes (NOLOCK) HT
					WHERE  ht.historytype = CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END

					INSERT INTO @TempHistory ( historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history,
								ActionType )
					SELECT ht.id AS historytype,
						   CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
								THEN @FromAllocationsAPID
								ELSE @FromID END AS attributeid,
							'PH' AS HistorySubType,
							getdate(),
							@vuserID,
							'Over12 was Removed by system '+
							+ ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.',
							'I'
						FROM HistoryTypes ht
						WHERE @FromMarkOverTweleve > 0
						  and @FromMarkOverTweleve < 9
						  and ht.historytype = CASE WHEN ISNULL(@FromAllocationsAPID,0) <> 0
												THEN 'AllocationScheduledPersonAddnlTeam'
												ELSE 'AllocationScheduledPerson' END


					INSERT INTO History ( historytype,
								attributeid,
								HistorySubType,
								[datetime],
								userid,
								history )
					SELECT historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history
						FROM @TempHistory

					SET @FromDuration = CASE WHEN @FromMarkWIAD = 1
											 THEN 0
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) END

					SET @ToDuration = CASE WHEN @FromMarkWIAD = 1
										   THEN 0
										   ELSE ISNULL(@ToDuration,0) - ISNULL(@ToBreakDuration,0) END

					DELETE @EditAllocationStatus;
					INSERT INTO @EditAllocationStatus

					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,@FromDuration,
										@ToDuration,@FromOverTimeHrs,0,@pNetLogin

					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;


					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,@FromAllocationsDutyID,
													 NULL,
													 0,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;


					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,@AllocationsDutyID,
													 @FromID,
													 0,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

			       EXEC	@ReturnValue = usp_UpdateWTD @FromID, @pNetLogin

			END

		-----------------------------------------------

		------------------------------------------------
		-- Delete Duty
		------------------------------------------------

          IF  (@EditType = 'DELETEDUTY')
		   BEGIN

			    IF (@FromID IS NULL )
				BEGIN
				  THROW 51001, 'From ID Cannot be NULL', 1;
				END

			    IF EXISTS (SELECT 1
				             FROM AllocationsDuties
				            WHERE AD_AllocationsDutyID = @FromID
  							  AND AD_DutyStatus = 1 )
				BEGIN
				  THROW 51002, 'Cannot Delete Assinged Duty', 1;
				END;

                 UPDATE AllocationsDuties
				    SET AD_DutyStatus = 9,
						AD_UpdatedBy = @vuserID,
						AD_UpdatedDate = GETUTCDATE()
				  WHERE AD_AllocationsDutyID = @FromID

				EXEC @ReturnValue = usp_CreateAllocationsUpdate NULL, @FromID,
												 0,
												 @PublishStatus,
												 @pNetLogin;

				IF ( ISNULL(@ReturnValue,1) <> 0 )
				BEGIN
					THROW 51000, 'Error while creating Duty update status', 1;
				END;

		   END

		------------------------------------------------
		-- Remove additional person from the week
		------------------------------------------------

          IF  (@EditType = 'REMOVEFROMWEEK')
		   BEGIN

			    IF (@pSchedulingPersonID IS NULL OR @pAllocationsID IS NULL)
				BEGIN
				  THROW 51050, 'Scheduled Person Cannot be NULL', 1;
				END

                IF EXISTS( SELECT 1
							 FROM Allocations AL
							INNER JOIN AllocationsAddPersons AAD on AAD.AAP_AllocationsID = AL_AllocationsID
							INNER JOIN AllocationsScheduledPersons asp on ASP_AllocationsSPID = AAP_AllocationsSPID
							INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
														   AND AL_AllocationsID = AD_AllocationsID
							WHERE AL_AllocationsID =  @pAllocationsID
							  AND AAP_SchedulingPersonID = @pSchedulingPersonID
							  AND AD_DutyType NOT IN (7,9) )
				  BEGIN
				   THROW 51052, 'You can not remove this person from this week as they have at least one duty assigned to them', 1;
				  END;

				  DELETE AllocationsAddPersons
				   WHERE AAP_AllocationsID =  @pAllocationsID
				     AND AAP_SchedulingPersonID = @pSchedulingPersonID

				  INSERT INTO AllocationsDelPersons(ADP_AllocationsID,
													ADP_SchedulingPersonID,
													ADP_Status,
													ADP_CreatedBy,
													ADP_CreatedDate)
									VALUES ( @pAllocationsID,
											 @pSchedulingPersonID,
											 1,
											 @vuserID,
											 GETUTCDATE()
											)

		   END

		------------------------------------------------
		-- Add additional person to the week
		------------------------------------------------

          IF  (@EditType = 'ADDPERSON')
		   BEGIN

			    IF ( ISNULL(@pAllocationsID,0) = 0 OR ISNULL(@pSchedulingPersonID,0) =0 )
				BEGIN
				  THROW 51060, 'Allocations ID or SchedulingPersonID cannot be null', 1;
				END

				DECLARE @Status INT;

				IF EXISTS ( SELECT 1
							  FROM AllocationsDelPersons
							 WHERE ADP_AllocationsID = @pAllocationsID
							   AND ADP_SchedulingPersonID = @pSchedulingPersonID )
				  BEGIN

				    DELETE AllocationsDelPersons
					 WHERE ADP_AllocationsID = @pAllocationsID
					   AND ADP_SchedulingPersonID = @pSchedulingPersonID

				  END
				 ELSE
				  BEGIN
					SELECT TOP 1 @Status = AAP_Status
					  FROM AllocationsAddPersons  AAP
					 WHERE AAP.AAP_AllocationsID = @pAllocationsID
					   AND AAP_SchedulingPersonID = @pSchedulingPersonID
				  END

                IF ( ISNULL(@Status,0) = 1)
				 BEGIN
				  THROW 51061, 'Scheduled Person Already Assigned in this week ', 1;
				 END

				 IF NOT EXISTS(
					SELECT 1
					  FROM ScheduledPersonTeam_LINK STL (nolock),
					       ( SELECT Min(td.ddatetime) AS startdate,
									Max(td.ddatetime) AS enddate
							   FROM Timedimension TD (nolock)
						      WHERE td.ixyearweek = @pWeekNumber
						   ) TD
					 WHERE STL.TeamID = @pteamID
					   AND STL.ScheduledPersonID = @pSchedulingPersonID
					   AND STL.scheduledType = 1
					   AND TD.startdate <=   ISNULL(STL.EndDate,TD.startdate)
					   AND TD.enddate >= ISNULL(STL.StartDate, TD.enddate) )
				 BEGIN
				  THROW 51062, 'Schedule Person Not assgined to this team in this week', 1;
				 END

				   INSERT INTO AllocationsAddPersons (  AAP_AllocationsID,
													    AAP_AllocationsSPID,
														AAP_SchedulingPersonID,
														AAP_Status,
														AAP_iDay,
														AAP_DutyDate,
														AAP_CreatedBy,
														AAP_CreatedDate)
						   SELECT AL_AllocationsID,
							      NULL,
								  SL.ScheduledPersonID,
								  1,
								  td.ixDayInWeek,
								  td.dDateTime,
								  @vuserID,
								  getutcdate()
							 FROM Allocations AL
							INNER JOIN TimeDimension TD on td.ixYearWeek = AL_WeekNumber
							INNER JOIN ScheduledPersonTeam_LINK SL on sl.TeamID = AL_SchedulingTeamID
							WHERE sl.scheduledType = 1
							  AND sl.IsHomeTeam IN (0,2)
							  AND sl.IsAvailable = 0
							  AND td.dDateTime between sl.StartDate and sl.EndDate
							  AND AL_AllocationsID = @pAllocationsID
							  AND SL.ScheduledPersonID = @pSchedulingPersonID

				   SET @RowCount = @@ROWCOUNT

				   INSERT INTO AllocationsAddPersons (  AAP_AllocationsID,
													    AAP_AllocationsSPID,
														AAP_SchedulingPersonID,
														AAP_Status,
														AAP_iDay,
														AAP_DutyDate,
														AAP_CreatedBy,
														AAP_CreatedDate)
						   SELECT AL.AL_AllocationsID,
							      NULL,
								  SL.ScheduledPersonID,
								  1,
								  AP.ASP_iDay,
								  AP.ASP_DutyDate,
								  @vuserID,
								  getutcdate()
							 FROM Allocations AL
							INNER JOIN ScheduledPersonTeam_LINK SL on sl.TeamID = AL_SchedulingTeamID
							INNER JOIN Allocations ALH ON ALH.AL_WeekNumber = AL.AL_WeekNumber
							INNER JOIN AllocationsScheduledPersons AP ON SL.ScheduledPersonID = AP.ASP_SchedulingPersonID
																	 AND ALH.AL_AllocationsID = AP.ASP_AllocationsID
							INNER JOIN ScheduledPersonTeam_LINK SLH ON SLH.ScheduledPersonID = AP.ASP_SchedulingPersonID
																	AND SLH.TeamID = ALH.AL_SchedulingTeamID
							WHERE sl.scheduledType = 1
							  AND sl.IsHomeTeam IN (0,2)
							  AND sl.IsAvailable = 1
							  AND SLH.IsHomeTeam = 1
							  AND SLH.scheduledType = 1
							  AND AP.ASP_DutyDate between SLH.StartDate and SLH.EndDate
							  AND AP.ASP_DutyDate between sl.StartDate and sl.EndDate
							  AND AL.AL_AllocationsID = @pAllocationsID
							  AND SL.ScheduledPersonID = @pSchedulingPersonID

						 SET @RowCount = CASE WHEN ISNULL(@RowCount,0) > 0 THEN @RowCount ELSE @@ROWCOUNT END

					     IF ( @RowCount > 0 )
						  BEGIN
							UPDATE AAP
							   SET AAP.AAP_AllocationsSPID = ASP_AllocationsSPID
							  FROM Allocations AL
							 INNER JOIN ScheduledPersonTeam_LINK SL ON SL.TeamID = AL.AL_SchedulingTeamID
							 INNER JOIN AllocationsScheduledPersons AP on AL.AL_AllocationsID = AP.ASP_AllocationsID
																	 AND SL.ScheduledPersonID = AP.ASP_SchedulingPersonID
							 INNER JOIN AllocationsAddPersons AAP ON sl.ScheduledPersonID = AAP.AAP_SchedulingPersonID
																AND AAP.AAP_iDay = ASP_iDay
							 INNER JOIN Allocations AL1 ON AL1.AL_AllocationsID = AAP_AllocationsID
													AND Al1.AL_WeekNumber = AL.AL_WeekNumber
							 WHERE AP.ASP_DutyDate BETWEEN SL.startdate AND SL.enddate
							   AND SL.IsHomeTeam = 1
							   AND SL.ScheduledPersonID = @pSchedulingPersonID
							   AND sl.scheduledType = 1
							   AND AL1.AL_AllocationsID = @pAllocationsID
						END

		   END

		------------------------------------------------
		-- Mark duty as Leave, Sick or Absent
		------------------------------------------------

          IF  (@EditType = 'MARKABSENT' OR @EditType = 'MARKSICK' OR @EditType = 'MARKLEAVE')
		   BEGIN

			    DECLARE @NewLeaveType INT,@DutyColourID INT;

			    IF ( @EditType = 'MARKABSENT' AND @pIsShiftleader = 0 )
				BEGIN
				  THROW 51070, 'This Role Cannot Mark a Duty as Absent', 1;
				END

			    IF ( ISNULL(@FromID,0) = 0 AND ISNULL(@pSchedulingPersonID,0) =  0)
				BEGIN
				  THROW 51071, 'From ID Cannot be NULL', 1;
				END

				 SELECT  @AllocationsID			 = AL_AllocationsID,
						 @SchedulingTeamID       = AL_SchedulingTeamID,
						 @vWeekNumber            = AL_WeekNumber
					FROM Allocations AL
				   WHERE AL_AllocationsID = @pAllocationsID;

				IF ( ISNULL(@FromID,0) = 0 AND @pSchedulingPersonID > 0 )
				 BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @AllocationsID,
													0,
													@pSchedulingPersonID,
													@pDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					END
					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

					SET @FromID = @AllocationsSPID;

				 END;

				 SELECT @FromIDay               = ASP_iDay,
				        @FromIsHomeTeam         = CASE WHEN ASP_AllocationsID <> @AllocationsID
												  	   THEN 0
													   ELSE 1 END,
						@FromMarkWIAD           = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@FromMarkActual         = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@FromDutyName           = AD_DutyName,
						@FromDutyTeamID         = ASP_DutyTeamID,
						@FromDutyDate           = ASP_DutyDate,
						@FromSPID               = ASP_SchedulingPersonID,
						@FromDuration           = CASE WHEN AD_DutyType IN (8,11)
													   THEN ASP_LeaveDuration
													   ELSE ISNULL(AD.AD_Duration,0) END,
						@FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromDisplayName        = UD_DisplayName,
						@FromStarttime          = AD_StartTimeSec,
						@FromEndTime            = AD_EndTimeSec,
						@FromLeaveStartTime     = ASP_LeaveStartTimeSec,
						@FromLeaveEndTime       = ASP_LeaveEndTimeSec,
						@FromLeaveType          = ASP_LeaveType,
						@FromLeaveStatus        = ASP_LeaveStatus,
						@FromStartTimeLocal	    = AD_DutyStartTimeLocal,
						@FromEndTimeLocal	    = AD_DutyEndTimeLocal,
						@FromChargingStatus     = ASP_ChargingStatus,
						@FromDutyType           = AD_DutyType,
						@FromAllocationsID      = ASP_AllocationsID,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus,
						@FromIsOverrideOver12	= AD_IsOverrideOver12,
						@FromBreakDuration		= AD_DutyBreakTime,
						@FromOverTimeHrs		= ASP_OverTimeHours
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @FromID;

			    IF ( @EditType <> 'MARKLEAVE' AND ISNULL(@FromIsHomeTeam,1) = 1
					   and ( ISNULL(@FromMarkWIAD,0) = 1 or ISNULL(@FromMarkActual,0) = 1) )
				BEGIN
				  THROW 51074, 'Person Assigned To a Different Team Cannot Mark Absent or Sick', 1;
				END

			    IF ( @EditType <> 'MARKLEAVE' AND ISNULL(@FromIsHomeTeam,1) = 0
					   and ( ISNULL(@FromMarkWIAD,0) = 0 AND ISNULL(@FromMarkActual,0) = 0) )
				BEGIN
				  THROW 51075, 'Person From a Different Team Cannot Mark Absent or Sick', 1;
				END

				SET @NewLeaveType = CASE when @EditType = 'MARKABSENT' THEN 7
 										 when @EditType = 'MARKSICK' AND @FromDutyType IN (7,9) AND @pDuration = 0 THEN 4
										 when @EditType = 'MARKSICK' AND @FromLeaveType IN (3,4) AND @pDuration = 0 THEN 4
										 when @EditType = 'MARKSICK' AND @FromDutyName = 'U' AND @pDuration > 0 THEN 3
										 when @EditType = 'MARKSICK' AND @FromDutyName like '-%' AND @pDuration = 0 THEN 5
										 when @EditType = 'MARKSICK' AND @FromDutyType NOT IN (7,9) AND @pDuration = 0 THEN 4
										 when @EditType = 'MARKSICK' AND @FromDutyType NOT IN (7,9) THEN 3
										 when @EditType = 'MARKLEAVE' and ISNULL(@pZeroLeave,0) = 1 THEN 2
										 when @EditType = 'MARKLEAVE' and ISNULL(@pZeroLeave,0) <> 1 THEN 1
								     END

				IF NOT EXISTS (	SELECT 1
								  FROM ScheduledPersonTeam_LINK STL (nolock)
								 WHERE STL.TeamID = @SchedulingTeamID
								   AND STL.ScheduledPersonID = @FromSPID
								   AND STL.scheduledType = 1
								   AND @FromDutyDate BETWEEN STL.StartDate AND STL.EndDate
                              )
				 BEGIN
				   THROW 51073, 'Scheduled Person Not assigned in this Team on this Day ', 1;
				 END;

				 IF ( @EditType = 'MARKSICK' )
				  BEGIN
		  		   SELECT @DutyColourID = MasterDutyColourID
				     FROM REF_MasterDutyColours MC (nolock)
				    INNER JOIN schedulingTeams SL ON SL.divisionId = MC.AreaID
					WHERE Upper(ColourName) like '%SICK%'
					  AND SL.schedulingTeamId = @SchedulingTeamId
			      END

				 IF ( @EditType = 'MARKABSENT' )
				  BEGIN
		  		   SELECT @DutyColourID = MasterDutyColourID
				     FROM REF_MasterDutyColours MC (nolock)
				    INNER JOIN schedulingTeams SL ON SL.divisionId = MC.AreaID
					WHERE Upper(ColourName) like '%ABSENT%'
					  AND SL.schedulingTeamId = @SchedulingTeamId
			      END

				 IF ( @EditType = 'MARKLEAVE' )
				  BEGIN
		  		   SELECT @DutyColourID = MasterDutyColourID
				     FROM REF_MasterDutyColours MC (nolock)
				    INNER JOIN schedulingTeams SL ON SL.divisionId = MC.AreaID
					WHERE Upper(ColourName) like '%LEAVE%'
					  AND SL.schedulingTeamId = @SchedulingTeamId
			      END

					INSERT INTO @TempHistory ( historytype,
							  attributeid,
							  HistorySubType,
							  CreateDateTime,
							  userid,
							  history ,
							  ActionType)
					SELECT ht.id AS historytype,
						   @FromID AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Over12 was Removed by system '+
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.',
						   'I'
					  FROM HistoryTypes HT
					 WHERE @FromMarkOverTweleve > 0 AND @FromMarkOverTweleve < 9
					   AND HT.historytype = 'AllocationScheduledPerson'

			    IF ( @FromDutyType < 7 AND ISNULL(@FromDuration,0) > 0 )
				BEGIN

	  			  INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
							  CreateDateTime, userid, history,
							  ActionType
							)
					SELECT ht.id AS historytype,
						   @FromAllocationsDutyID AS attributeid,
						   'DH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', UnAllocated Duty ( '
						   + @FromDutyName
						   +' from '+@FromDisplayName
						   +' )',
						   'I'
					FROM   HistoryTypes ht
					WHERE  historytype = 'AllocationDuty'

                END


	  			  INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
							  CreateDateTime, userid, history,ActionType
							)
					SELECT ht.id AS historytype,
						   @FromID AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   CASE WHEN @EditType IN ( 'MARKABSENT','MARKSICK')
						    THEN  'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
                           + ' by ' + @vname + ', '
						   WHEN  @EditType = 'MARKLEAVE'
						   THEN 'Leave approved by '+ @vname +' on '
						        + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'. '
						   END
						   + CASE WHEN @EditType = 'MARKABSENT' THEN 'Marked Absent.'
 						          WHEN @EditType = 'MARKSICK' THEN @psicknessHistory
								  WHEN @EditType = 'MARKLEAVE' THEN ISNULL(@pLeaveHistory,'.') END,
							'I'
					FROM  HistoryTypes ht
			       WHERE  historytype = 'AllocationScheduledPerson'

					  IF ( @EditType = 'MARKLEAVE' AND @pLeaveStartTime = 0 AND @pLeaveEndTime = 0
					       AND @FromLeaveType = 6)
					   BEGIN

						 DELETE Allocationsjobs
						  WHERE AJ_AllocationsDutyID = @FromAllocationsDutyID
							AND AJ_JobName = 'Leave'

					   END

						INSERT INTO History ( historytype,
								  attributeid,
								  HistorySubType,
								  [datetime],
								  userid,
								  history )
						SELECT historytype,
								  attributeid,
								  HistorySubType,
								  CreateDateTime,
								  userid,
								  history
						  FROM @TempHistory
						  WHERE ActionType ='I'

				 IF ( @EditType = 'MARKABSENT' )
				  BEGIN
					 INSERT INTO AllocationsDuties
							(
								AD_AllocationsID,
								AD_DutyName,
								AD_Duration,
								AD_iDay,
								AD_StartTimeSec,
								AD_EndTimeSec,
								AD_DutyDate,
								AD_DutyStartTimeUTC,
								AD_DutyEndTimeUTC,
								AD_DutyStartTimeLocal,
								AD_DutyEndTimeLocal,
								AD_DutyType,
								AD_DutyStatus,
								AD_DutyColourID,
								AD_isAttention,
								AD_isRequest,
								AD_PlannedDuration,
								AD_PlannedDutyBreakTime,
								AD_IsNeedCovering,
								AD_IsOverrideOver12,
								AD_IsDutyEdited,
								AD_IsEditedDutyAttention,
								AD_CreatedBy,
								AD_CreatedDate,
								AD_UpdatedBy,
								AD_UpdatedDate
							 )
					  SELECT 	AD_AllocationsID,
								'Absent',
								AD_Duration,
								AD_iDay,
								AD_StartTimeSec,
								AD_EndTimeSec,
								AD_DutyDate,
								AD_DutyStartTimeUTC,
								AD_DutyEndTimeUTC,
								AD_DutyStartTimeLocal,
								AD_DutyEndTimeLocal,
								11,
								AD_DutyStatus,
								@DutyColourID,
								0,
								0,
								AD_PlannedDuration,
								AD_PlannedDutyBreakTime,
								1,
								1,
								0,
								case when @pIsShiftleader = 1 then 1 else NULL end,
								AD_CreatedBy,
								AD_CreatedDate,
								 @vuserID,
								 getutcdate()
						   FROM AllocationsDuties
						  WHERE AD_AllocationsDutyID = @FromAllocationsDutyID

				      SET @AllocationsDutyID = @@IDENTITY

					  SELECT @SchedulingTeamID = AL_SchedulingTeamID
						FROM Allocations
					   WHERE AL_AllocationsID = @AllocationsID

				   END
				 ELSE
				  BEGIN
					  SELECT @SchedulingTeamID = AL_SchedulingTeamID,
							 @AllocationsDutyID = AD_AllocationsDutyID
						FROM Allocations AL
					   INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
					   WHERE AL.AL_AllocationsID = @AllocationsID
						 AND AD_DutyDate = @FromDutyDate
						 AND AD_DutyType = 8
				  END

				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = @AllocationsDutyID,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_LeaveType = @NewLeaveType,
						  ASP_LeaveDuration = @pDuration,
						  ASP_LeaveColourID = @DutyColourID,
						  ASP_LeaveStatus = 1,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = 0,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_ChargingStatus = CASE WHEN @pIsShiftleader = 1 AND @EditType = 'MARKABSENT'
													THEN 0
													ELSE ASP_ChargingStatus END,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID =  @FromID

				 IF ( @FromDutyType NOT IN (7,8) )
				  BEGIN
					UPDATE AllocationsDuties
					   SET AD_DutyStatus = CASE WHEN ISNULL(AD_Duration,0) = 0
												THEN 9
												WHEN ISNULL(AD_IsNeedCovering,1) = 0
												THEN 9
												WHEN ISNULL(AD_Duration,0) > 0
												THEN 0
											END,
					       AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID = @FromAllocationsDutyID;
				  END
					  
					SET @ToDuration =  ISNULL(@pDuration,0) 
					SET @FromDuration = CASE WHEN @FromMarkWIAD = 1 
											 THEN 0 
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) 
											 END

					DELETE @EditAllocationStatus;
					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,
										@FromDuration,@ToDuration,
										@FromOverTimeHrs,0,@pNetLogin
					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,@AllocationsDutyID,
													 @FromID,
													 @PublishStatus,
													 @pNetLogin;


					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,@FromAllocationsDutyID,
													 0,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

				   IF ( @pIsShiftleader = 1 AND @EditType = 'MARKABSENT' )
				   BEGIN
						DELETE from ChargingDutyMapping_Link where AllocationId = @FromID
				   END

			    EXEC	@ReturnValue = usp_UpdateWTD @FromID, @pNetLogin

			    IF ( @EditType = 'MARKLEAVE' )
				 BEGIN
				  
				   IF ( @FromDutyType < 7 )
					EXEC usp_mod_PublishIndividulAllocations @pAllocationsID, @FromAllocationsDutyID , 0

					EXEC usp_mod_PublishIndividulAllocations @pAllocationsID, 0 , @FromID

				END 

		   END


		------------------------------------------------
		-- Mark duty as Leave, Sick or Absent
		------------------------------------------------

          IF  (@EditType = 'PDL')
		   BEGIN

			    IF (@FromID IS NULL OR @pLeaveStartTime IS NULL OR @pLeaveEndTime IS NULL)
				BEGIN
				  THROW 51071, 'From ID, Leave Start Time and Leave End Time Cannot be NULL', 1;
				END


				 SELECT  @AllocationsID			 = AL_AllocationsID,
						 @SchedulingTeamID       = AL_SchedulingTeamID,
						 @vWeekNumber            = AL_WeekNumber
					FROM Allocations AL
				   WHERE AL_AllocationsID = @pAllocationsID;

				 SELECT @FromIDay               = ASP_iDay,
				        @FromIsHomeTeam         = CASE WHEN ASP_AllocationsID <> @AllocationsID
												  	   THEN 0
													   ELSE 1 END,
						@FromMarkWIAD           = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@FromMarkActual         = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@FromDutyName           = AD_DutyName,
						@FromDutyTeamID         = ASP_DutyTeamID,
						@FromDutyDate           = ASP_DutyDate,
						@FromSPID               = ASP_SchedulingPersonID,
						@FromDuration           = AD_Duration,
						@FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromDisplayName        = UD_DisplayName,
						@FromStarttime          = AD_StartTimeSec,
						@FromEndTime            = AD_EndTimeSec,
						@FromLeaveStartTime     = ASP_LeaveStartTimeSec,
						@FromLeaveEndTime       = ASP_LeaveEndTimeSec,
						@FromLeaveType          = ASP_LeaveType,
						@FromLeaveStatus        = ASP_LeaveStatus,
						@FromStartTimeLocal	    = AD_DutyStartTimeLocal,
						@FromEndTimeLocal	    = AD_DutyEndTimeLocal,
						@FromChargingStatus     = ASP_ChargingStatus,
						@FromDutyType           = AD_DutyType,
						@FromAllocationsID      = ASP_AllocationsID,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus,
						@FromBreakDuration		= AD_DutyBreakTime,
						@FromOverTimeHrs		= ASP_OverTimeHours
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @FromID;

				IF ( @FromDutyName IS NULL)
				 BEGIN
				   THROW 51072, 'There is problem with the data. Underlying data has been modified. Please refresh the page and try again.', 1;
				 END;

				IF NOT EXISTS (	SELECT 1
								  FROM ScheduledPersonTeam_LINK STL (nolock)
								 WHERE STL.TeamID = @SchedulingTeamID
								   AND STL.ScheduledPersonID = @FromSPID
								   AND STL.scheduledType = 1
								   AND @FromDutyDate BETWEEN STL.StartDate AND STL.EndDate
                              )
				 BEGIN
				   THROW 51073, 'Scheduled Person Not assigned in this Team on this Day ', 1;
				 END;

				 SET @LeaveStartTimeLocal =  dbo.ufn_ConvertToDateTime(CASE WHEN ISNULL(@pOverNightFlag,0) = 1 AND @pLeaveEndTime < @pLeaveStartTime
																			THEN DATEADD(DAY,1,@FromDutyDate)
																			ELSE @FromDutyDate END,@pLeaveStartTime)

				 SET @LeaveEndTimeLocal =  dbo.ufn_ConvertToDateTime(CASE WHEN ISNULL(@pOverNightFlag,0) = 1
																			THEN DATEADD(DAY,1,@FromDutyDate)
																			ELSE @FromDutyDate END,@pLeaveEndTime)

                 IF ( CAST(@FromDuration - DATEDIFF(SECOND,@LeaveStartTimeLocal,@LeaveEndTimeLocal) AS FLOAT)/CAST(3600 AS FLOAT) <= 12 )
				  BEGIN

					INSERT INTO @TempHistory ( historytype,
							  attributeid,
							  HistorySubType,
							  CreateDateTime,
							  userid,
							  history )
					SELECT ht.id AS historytype,
						   @FromID AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Over12 was Removed by system On '+
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.'
					  FROM HistoryTypes HT
					 WHERE @FromMarkOverTweleve > 0 AND @FromMarkOverTweleve  < 9
					   AND HT.historytype = 'AllocationScheduledPerson'
				  END

	  			  INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
							  CreateDateTime, userid, history
							)
					SELECT ht.id AS historytype,
						   @FromID AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Leave approved by '+ @vname + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
                            +  '. '+ISNULL(@pLeaveHistory,'.')
					FROM  HistoryTypes HT
				   WHERE  historytype = 'AllocationScheduledPerson'

				   UPDATE AllocationsScheduledPersons
				      SET ASP_LeaveType = 6,
						  ASP_LeaveStatus = 1,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_LeaveStartTimeSec = @pLeaveStartTime,
						  ASP_LeaveEndTimeSec = @pLeaveEndTime,
						  ASP_LeaveStartTimeLocal = @LeaveStartTimeLocal,
						  ASP_LeaveEndTimeLocal = @LeaveEndTimeLocal,
						  ASP_OverTwelveStatus = CASE WHEN CAST(@FromDuration - DATEDIFF(SECOND,@LeaveStartTimeLocal,@LeaveEndTimeLocal) AS FLOAT)/CAST(3600 AS FLOAT) > 12
															then 1 else 0
													   END,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID =  @FromID

				  IF EXISTS (  SELECT 1
								 FROM AllocationsJobs
							    WHERE AJ_AllocationsDutyID = @FromAllocationsDutyID
								  AND AJ_JobName <> 'Leave'
								  AND ( @LeaveStartTimeLocal BETWEEN AJ_JobStartTimeLocal AND AJ_JobEndTimeLocal
								    OR @LeaveEndTimeLocal BETWEEN AJ_JobStartTimeLocal AND AJ_JobEndTimeLocal)
							)
					BEGIN

					  SELECT @SchedulingTeamID = AL_SchedulingTeamID,
							 @AllocationsDutyID = AD_AllocationsDutyID
						FROM Allocations AL
					   INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
					   WHERE AL.AL_AllocationsID = @AllocationsID
						 AND AD_DutyDate = @FromDutyDate
						 AND AD_DutyType = 7

					  UPDATE AllocationsJobs
					     SET AJ_AllocationsDutyID = @AllocationsDutyID
					   WHERE AJ_AllocationsDutyID = @FromAllocationsDutyID
						 AND AJ_JobName <> 'Leave'
						 AND ( @LeaveStartTimeLocal BETWEEN AJ_JobStartTimeLocal AND AJ_JobEndTimeLocal
						    OR @LeaveEndTimeLocal BETWEEN AJ_JobStartTimeLocal AND AJ_JobEndTimeLocal)

					END

				  DECLARE @JobLeaveStartTime INT, @JobLeaveEndTime INT, @JobID INT;

				  SELECT @JobID = AJ_AllocateJobID,
						 @JobLeaveStartTime = AJ_JobStartTimeSec,
						 @JobLeaveEndTime = AJ_JobEndTimeSec
					FROM AllocationsJobs
				   WHERE AJ_AllocationsDutyID = @FromAllocationsDutyID
					 AND AJ_JobName = 'Leave'

				  IF ( ISNULL(@JobID,0) = 0 )
				   BEGIN

					   INSERT INTO Allocationsjobs(
								AJ_AllocationsDutyID,
								AJ_JobName,
								AJ_JobStartTimeSec,
								AJ_JobEndTimeSec,
								AJ_JobStartTimeLocal,
								AJ_JobEndTimeLocal,
								AJ_JobStatus)
						SELECT  @FromAllocationsDutyID, 'Leave',
								@pLeaveStartTime, @pLeaveEndTime,
								@LeaveStartTimeLocal,@LeaveEndTimeLocal, 1

				    END

				  IF ( ISNULL(@JobID,0) >  0 AND ( @JobLeaveStartTime <> @pLeaveStartTime
												OR @JobLeaveEndTime <> @pLeaveEndTime ))
				   BEGIN

					 UPDATE AllocationsJobs
					    SET AJ_JobStartTimeSec = @pLeaveStartTime,
							AJ_JobEndTimeSec = @pLeaveEndTime,
							AJ_JobStartTimeLocal = @LeaveStartTimeLocal,
							AJ_JobEndTimeLocal = @LeaveEndTimeLocal
					  WHERE AJ_AllocateJobID = @JobID

				   END

						INSERT INTO History ( historytype,
								  attributeid,
								  HistorySubType,
								  [datetime],
								  userid,
								  history )
						SELECT historytype,
								  attributeid,
								  HistorySubType,
								  CreateDateTime,
								  userid,
								  history
						  FROM @TempHistory

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID, @FromAllocationsDutyID,
													 @FromID,
													 0,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

			   EXEC	@ReturnValue = usp_UpdateWTD @FromID, @pNetLogin

		   END


        -----------------------------------------------------------
        -- Unmark Absent
        -----------------------------------------------------------

          IF  (@EditType = 'UNMARKABSENT')
 		    BEGIN

			    IF (@FromID IS NULL )
				BEGIN
				  THROW 51030, 'From ID Cannot be NULL', 1;
				END

				 SELECT  @AllocationsID			 = AL_AllocationsID,
						 @SchedulingTeamID       = AL_SchedulingTeamID,
						 @vWeekNumber            = AL_WeekNumber
					FROM Allocations AL
				   WHERE AL_AllocationsID = @pAllocationsID;

				 SELECT @FromIDay               = ASP_iDay,
				        @FromIsHomeTeam         = CASE WHEN ASP_AllocationsID <> @AllocationsID
												  	   THEN 0
													   ELSE 1 END,
						@FromMarkWIAD           = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
						@FromMarkActual         = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
						@FromDutyName           = AD_DutyName,
						@FromDutyTeamID         = ASP_DutyTeamID,
						@FromDutyDate           = ASP_DutyDate,
						@FromSPID               = ASP_SchedulingPersonID,
						@FromDuration           = AD_Duration,
						@FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromDisplayName        = UD_DisplayName,
						@FromStarttime          = AD_StartTimeSec,
						@FromEndTime            = AD_EndTimeSec,
						@FromLeaveStartTime     = ASP_LeaveStartTimeSec,
						@FromLeaveEndTime       = ASP_LeaveEndTimeSec,
						@FromLeaveType          = ASP_LeaveType,
						@FromLeaveStatus        = ASP_LeaveStatus,
						@FromStartTimeLocal	    = AD_DutyStartTimeLocal,
						@FromEndTimeLocal	    = AD_DutyEndTimeLocal,
						@FromChargingStatus     = ASP_ChargingStatus,
						@FromDutyType           = AD_DutyType,
						@FromAllocationsID      = ASP_AllocationsID,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
				  WHERE ASP_AllocationsSPID = @FromID;

				IF ( @FromDutyName IS NULL )
				 BEGIN
				   THROW 51031, 'There is problem with the data. Underlying data has been modified. Please refresh the page and try again.', 1;
				 END;

				IF ( @FromLeaveType <>  7)
				 BEGIN
				   THROW 51033, 'Duty is not marked as Absent.', 1;
				 END;

			    IF ( ISNULL(@FromDutyTeamID,0) <> 0 AND ( ISNULL(@FromDutyTeamID,0) <>  @SchedulingTeamID )  )
				BEGIN
				  THROW 51032, 'Duty Assigned From a different team on this day ', 1;
				END

				SET @IsFreelancer = dbo.ufn_IsFreeLancer(@FromSPID,@FromDutyDate)

			    IF ( ISNULL(@FromIsHomeTeam,1) = 0 AND ISNULL(@IsFreelancer,0) = 0)
				BEGIN
				  THROW 51034, 'It is not possible to Unmark absent for additional person', 1;
				END

                   IF ( ISNULL(@FromLeaveStartTime,0) > 0 OR  ISNULL(@FromLeaveEndTime,0) > 0 )
				    BEGIN

					    DECLARE @LeaveID INT

						SELECT @LeaveID = LA.ID
						  FROM LeaveApplications LA
						 WHERE LA.dDate = @FromDutyDate
						   AND LA.SchedulingPersonID = @FromSPID

						DELETE ref_LeaveApplications_Amounts
						 WHERE ApplicationID = @LeaveID;

						UPDATE LeaveApplications
						   SET Approved = 0,
						       Deleted = 1,
							   CountLeave = 1,
							   ZeroLeave = 0,
							   Totalhrs=NULL,
							   [Sent] = 0,
							   LastModDate = getutcdate(),
							   LastModBy = @vuserID
						 WHERE ID = @LeaveID;

						 DELETE AllocationsJobs
						  WHERE AJ_AllocationsDutyID = @FromAllocationsDutyID
							AND AJ_JobName = 'Leave';

	  					  INSERT INTO history
									( historytype, attributeid, HistorySubType,
									  datetime, userid, history
									)
							SELECT ht.id AS historytype,
								   @LeaveID AS attributeid,
								   NULL,
								   getdate(),
								   @vuserID,
								   'PDL leave deleted On '
								   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
									   + ' By ' + @vname + ', '
								   + 'due to unmark absent.'
							FROM  HistoryTypes HT
							WHERE  historytype = 'LeaveApplication'

					END

				  SELECT @SchedulingTeamID = AL_SchedulingTeamID,
						 @AllocationsDutyID = AD_AllocationsDutyID
					FROM Allocations AL
				   INNER JOIN AllocationsDuties AD on AL.AL_AllocationsID = AD_AllocationsID
				   WHERE AL.AL_AllocationsID = @AllocationsID
				     AND AD_DutyDate = @FromDutyDate
					 AND AD_DutyType = 7

				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = @AllocationsDutyID,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_LeaveType = NULL,
						  ASP_LeaveDuration = NULL,
						  ASP_LeaveStatus = NULL,
						  ASP_LeaveColourID = NULL,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID =  @FromID

				 IF ( @FromDutyType = 11 )
				  BEGIN

				    UPDATE AllocationsDuties
					   SET AD_DutyStatus = 9,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					 WHERE AD_AllocationsDutyID = @FromAllocationsDutyID

				  END

	  			  INSERT INTO history
							( historytype, attributeid, HistorySubType,
							  datetime, userid, history
							)
					SELECT ht.id AS historytype,
						   @FromID AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
                               + ' By ' + @vname + ', '
						   + 'Unmarked Absent.'
					FROM  HistoryTypes HT
					WHERE  historytype = 'AllocationScheduledPerson'

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,@AllocationsDutyID,
													 @FromID,
													 0,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000,'Error while creating Duty update status' , 1;
					END;

                EXEC @ReturnValue = usp_UpdateWTD @FromID, @pNetLogin

		    END



		------------------------------------------------------------
		-- Assign duty directly to a additional person to the week
		------------------------------------------------------------

          IF  (@EditType = 'ASSGNTOADDPERSON' )
		   BEGIN

			    IF (@FromID IS NULL OR @pSchedulingPersonID IS NULL )
				BEGIN
				  THROW 51080, 'From ID or Schedule Person ID Cannot be NULL', 1;
				END

				DECLARE @HomeTeamID  INT;

                SELECT @FromDutyName = AD_DutyName,
				       @FromSPID = ASP_SchedulingPersonID,
					   @FromIDay = ASP_iDay,
					   @vWeekNumber = AL_WeekNumber,
					   @SchedulingTeamId = AL_SchedulingTeamID,
					   @FromDutyDate = AD_DutyDate,
					   @FromStarttime = AD_StartTimeSec,
					   @FromEndTime = AD_EndTimeSec,
					   @FromStartTimeLocal = AD_DutyStartTimeLocal,
					   @FromEndTimeLocal = AD_DutyEndTimeLocal ,
					   @FromAllocationsID = ASP_AllocationsID,
					   @AllocationsSPID = ASP_AllocationsSPID,
					   @AllocationsID	= AL_AllocationsID,
					   @FromMarkOverTweleve = ASP_OverTwelveStatus,
					   @FromDuration = AD_Duration,
					   @FromBreakDuration = AD_DutyBreakTime
                  FROM Allocations AL
				 INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
				  LEFT JOIN AllocationsScheduledPersons asp on ASP_AllocationsDutyID = AD_AllocationsDutyID
				 WHERE AD_AllocationsDutyID = @FromID
				   AND AD_DutyType < 7

			    IF ( ISNULL(@FromSPID,0) > 0 )
				 BEGIN
				  THROW 51081, 'Cannot assign the duty to this Scheduled Person through this process. Use Assign Option', 1;
				 END

			    IF ( @FromDutyName IS NULL )
				 BEGIN
				  THROW 51081, 'Duty does not exists', 1;
				 END

					SELECT @HomeTeamID = TeamID
					  FROM ScheduledPersonTeam_LINK
					 WHERE ScheduledPersonID = @pSchedulingPersonID
					   AND scheduledType = 1
					   AND IsHomeTeam = 1
					   AND @FromDutyDate between StartDate AND EndDate

				 SELECT @FromDisplayName = UD_DisplayName
				   FROM UserDetails AS sp (nolock)
				  WHERE UD_UserID = @pSchedulingPersonID

				IF EXISTS ( SELECT 1
							  FROM AllocationsDelPersons
							 WHERE ADP_AllocationsID = @AllocationsID
							   AND ADP_SchedulingPersonID = @pSchedulingPersonID
						   )
				  BEGIN

				    DELETE AllocationsDelPersons
					 WHERE ADP_AllocationsID = @AllocationsID
					   AND ADP_SchedulingPersonID = @pSchedulingPersonID

				   END

				 IF NOT EXISTS(
					SELECT 1
					  FROM ScheduledPersonTeam_LINK STL (nolock)
					 WHERE STL.TeamID = @SchedulingTeamID
					   AND STL.ScheduledPersonID = @pSchedulingPersonID
					   AND STL.scheduledType = 1
					   AND @FromDutyDate BETWEEN STL.StartDate AND STL.EndDate )
				 BEGIN
				  THROW 51062, 'Schedule Person Not assgined to this team in this week', 1;
				 END

				IF ( ISNULL(@AllocationsSPID,0) = 0)
				 BEGIN

				     SELECT @AllocationsSPID = ASP_AllocationsSPID,
							@ToDutyType = AD_DutyType,
							@ToDuration = AD_Duration,
							@ToBreakDuration = AD_DutyBreakTime,
							@ToMarkWIAD	= CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
							@ToAllocationsID = AL_AllocationsID
					   FROM Allocations AL
					  INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
					  INNER JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
					  WHERE AL_WeekNumber = @vWeekNumber
						AND AL_SchedulingTeamID = @HomeTeamID
						AND ASP_SchedulingPersonID = @pSchedulingPersonID
						AND ASP_iDay = @FromIDay

					IF ( ISNULL(@AllocationsSPID,0) = 0)
					 BEGIN

					   SELECT @ToAllocationsID = AL_AllocationsID
						 FROM Allocations
						WHERE AL_WeekNumber = @vWeekNumber
						  AND AL_SchedulingTeamID = @HomeTeamID

						IF ( ISNULL(@ToAllocationsID,0) = 0 )
						 BEGIN
							 INSERT INTO Allocations
									( AL_WeekNumber,
									  AL_SchedulingTeamId,
									  AL_Status,
									  AL_CreatedBy,
									  AL_CreatedDate)
							 Values ( @vWeekNumber,
									  @HomeTeamID,
									  9,
									  @vuserID,
									  getutcdate()
									)

							SET @ToAllocationsID = @@Identity

							 INSERT INTO AllocationsDuties
									(
									AD_AllocationsID,
									AD_DutyName,
									AD_iDay,
									AD_DutyDate,
									AD_DutyStartTimeUTC,
									AD_DutyEndTimeUTC,
									AD_DutyStartTimeLocal,
									AD_DutyEndTimeLocal,
									AD_DutyType,
									AD_DutyStatus,
									AD_CreatedBy,
									AD_CreatedDate
									 )
							  SELECT @ToAllocationsID AS allocationid,
									 'U',
									 ixDayInWeek,
									 dDateTime,
									 NULL,
									 NULL,
									 NULL,
									 NULL,
									 7,
									 1,
									 @vuserID,
									 getutcdate()
								FROM TimeDimension
							   where ixYearWeek = @vWeekNumber

						 END

						 BEGIN

							EXEC @ReturnValue = usp_CreateScheduledPerson  @ToAllocationsID,
															@FromID,
															@pSchedulingPersonID,
															@FromDutyDate,
															@pNetLogin,
															@AllocationsSPID = @AllocationsSPID OUTPUT

							IF ( ISNULL(@ReturnValue,0) > 0 )
							BEGIN
								THROW 51000, 'Error while creating scheduled person allocation', 1;
							END
							IF (@AllocationsSPID ) = 0
							  BEGIN
								THROW 51001, 'Error while creating allocation for scheduled person', 1;
							  END
						 END

					 END

				   END

				   INSERT INTO AllocationsAddPersons (  AAP_AllocationsID,
													    AAP_AllocationsSPID,
														AAP_SchedulingPersonID,
														AAP_Status,
														AAP_iDay,
														AAP_DutyDate,
														AAP_CreatedBy,
														AAP_CreatedDate)
						   SELECT AL_AllocationsID,
							      NULL,
								  SL.ScheduledPersonID,
								  1,
								  td.ixDayInWeek,
								  td.dDateTime,
								  @vuserID,
								  getutcdate()
							 FROM Allocations AL
							INNER JOIN TimeDimension TD on td.ixYearWeek = AL_WeekNumber
							INNER JOIN ScheduledPersonTeam_LINK SL on sl.TeamID = AL_SchedulingTeamID
							WHERE sl.scheduledType = 1
							  AND sl.IsHomeTeam IN (0,2)
							  AND sl.IsAvailable = 0
							  AND td.dDateTime between sl.StartDate and sl.EndDate
							  AND AL_AllocationsID = @AllocationsID
							  AND SL.ScheduledPersonID = @pSchedulingPersonID
							  AND NOT EXISTS (
												SELECT 1
												  FROM AllocationsAddPersons AAP
												 WHERE AAP.AAP_SchedulingPersonID = SL.ScheduledPersonID
												   AND AAP.AAP_AllocationsID = AL_AllocationsID
												   AND AAP.AAP_iDay = td.ixDayInWeek
												   AND AAP.AAP_Status <> 9
											 )


							UPDATE AAP
							   SET AAP.AAP_AllocationsSPID = ASP_AllocationsSPID
							  FROM Allocations AL
							 INNER JOIN ScheduledPersonTeam_LINK SL ON SL.TeamID = AL.AL_SchedulingTeamID
							 INNER JOIN AllocationsScheduledPersons AP on AL.AL_AllocationsID = AP.ASP_AllocationsID
																	 AND SL.ScheduledPersonID = AP.ASP_SchedulingPersonID
							 INNER JOIN AllocationsAddPersons AAP ON sl.ScheduledPersonID = AAP.AAP_SchedulingPersonID
																AND AAP.AAP_iDay = ASP_iDay
							 INNER JOIN Allocations AL1 ON AL1.AL_AllocationsID = AAP_AllocationsID
													AND Al1.AL_WeekNumber = AL.AL_WeekNumber
							 WHERE AP.ASP_DutyDate BETWEEN SL.startdate AND SL.enddate
							   AND SL.IsHomeTeam = 1
							   AND SL.ScheduledPersonID = @pSchedulingPersonID
							   AND sl.scheduledType = 1
							   AND AL1.AL_AllocationsID = @AllocationsID
							   AND AAP_DutyDate = @FromDutyDate

					SELECT @IsDutyOverLap = IsDutyOverLap,
						   @prevOverlapMsg = PrevDayOvrlapMsg,
						   @nextOverlapMsg = NextDayOvrlapMsg
					  FROM ufn_check_DutyOverLap(@AllocationsSPID,@FromStartTimeLocal,@FromEndTimeLocal)

					SET @OvreLapMsg = ISNULL(@prevOverlapMsg,'')+' '+ISNULL(@nextOverlapMsg,'')

					IF ( @IsDutyOverLap = 1 )
					 BEGIN
					  THROW 51011, @OvreLapMsg, 1;
					 END;

				 IF ( ISNULL(@ToDutyType,0) NOT IN (0,2,7,9) )
					 BEGIN
					  THROW 51082, 'Cannot assign the duty as duty already assigned in a different team', 1;
					 END

				     SELECT @FromAllocationsAPID = AP.AAP_AllocationsAPID
					  FROM AllocationsAddPersons AP
					 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_SchedulingPersonID = AP.AAP_SchedulingPersonID
															   AND ASP.ASP_DutyDate = AP.AAP_DutyDate
					  WHERE ASP.ASP_SchedulingPersonID = @pSchedulingPersonID
					    AND ASP.ASP_DutyDate = @FromDutyDate
						AND AP.AAP_AllocationsID = @AllocationsID

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
					SELECT ht.id AS historytype,
						   @FromID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Allocated from UnAllocated Duties to '
						   + @FromDisplayName + ' (Swapped '+ @FromDutyName + ' to '
						   + @ToDutyName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationDuty'

				  IF ( @ToDutyType < 7 )
				   BEGIN
				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
						SELECT ht.id AS historytype,
							   @ToAllocationsDutyID AS attributeid,
							   'DH' AS HistorySubType,
							   getdate(),
							   @vuserID,
							   'Modified On '
							   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							   + ' By ' + @vname + ', Allocated from UnAllocated Duties to '
							   + @FromDisplayName + ' (Swapped '+ @FromDutyName + ' to '
							   + @ToDutyName +')',
								   'I'
						FROM HistoryTypes ht
					   WHERE ht.historytype = 'AllocationDuty'
					 END

				    INSERT INTO @TempHistory
							(
										historytype,
										attributeid,
										HistorySubType,
										CreateDateTime,
										userid,
										history,
										ActionType
							)
					SELECT ht.id AS historytype,
						   @FromAllocationsAPID AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified On '
						   + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', Drag and Drop (Swapped duty ['
						   + @ToDutyName +'] on '+ @ToDisplayName + ' to ['
						   + @FromDutyName +'] from ' + @FromDisplayName +')',
						   'I'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationScheduledPersonAddnlTeam'


				   UPDATE AllocationsScheduledPersons
				      SET ASP_AllocationsDutyID = @FromID,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_WIADStatus = CASE WHEN ISNULL(ASP_WIADStatus,0) = 0 THEN 2 ELSE ASP_WIADStatus END,
						  ASP_OverTwelveStatus = case when @FromDuration > 43200
						                           and isnull(@FromIsOverrideOver12,1) = 1
						                          then 1 else 0 end,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_ChargingStatus = CASE WHEN @pIsShiftleader = 1
													THEN 0
													ELSE ASP_ChargingStatus END,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
					WHERE ASP_AllocationsSPID = @AllocationsSPID

					UPDATE AllocationsDuties
					   SET AD_DutyStatus = 1,
					       AD_IsEditedDutyAttention = CASE WHEN ISNULL(@pIsShiftleader,0) = 1
															THEN 1
															ELSE AD_IsEditedDutyAttention END,
						   AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					WHERE AD_AllocationsDutyID = @FromID;

					EXEC @ReturnValue = usp_Create_HistoryAddPerson @AllocationsAPID

					INSERT INTO History ( historytype,
								attributeid,
								HistorySubType,
								[datetime],
								userid,
								history )
					SELECT historytype,
								attributeid,
								HistorySubType,
								CreateDateTime,
								userid,
								history
						FROM @TempHistory

					SET @ToDuration = CASE WHEN @ToMarkWIAD = 1
										   THEN 0
										   ELSE ISNULL(@ToDuration,0) - ISNULL(@ToBreakDuration,0) END
					SET @FromDuration = ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0)

					DELETE @EditAllocationStatus;
					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @pSchedulingPersonID,@ToDuration,
										@FromDuration,0,0,@pNetLogin

					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

				    EXEC	@ReturnValue = usp_UpdateWTD @AllocationsSPID, @pNetLogin

					EXEC @ReturnValue = usp_CreateAllocationsUpdate NULL,@FromID,
													 @AllocationsSPID,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END;

               IF ( ISNULL(@pIsShiftleader,0) = 1 )
                BEGIN
				  EXEC usp_mod_PublishIndividulAllocations @AllocationsID, @FromID , @AllocationsSPID
				END


		   END

		------------------------------------------------------------
		-- Mark Duty as WIAD OR Absent
		------------------------------------------------------------

           IF  ( @EditType = 'MARKWIAD' OR @EditType = 'MARKACTUAL' )
		   BEGIN

			    IF (@FromID IS NULL AND ISNULL(@pSchedulingPersonID,0) = 0 AND @pDutyDate IS NULL or @pAllocationsID IS NULL)
				BEGIN
				  THROW 51090, 'From ID Cannot be NULL', 1;
				END;

				IF  ( @EditType = 'MARKWIAD' AND @pMarkWIAD IS NULL )
				BEGIN
				  THROW 51091, 'Mark WIAD parameter is not valid', 1;
				END;

				IF ( @EditType = 'MARKACTUAL' AND @pMarkActual IS NULL )
				BEGIN
				  THROW 51092, 'Mark Actual Parameter is not valid', 1;
				END;

				SET @AllocationsID = @pAllocationsID

				IF ( ISNULL(@FromID,0) = 0 AND @pSchedulingPersonID > 0 )
				 BEGIN

					EXEC @ReturnValue =  usp_CreateScheduledPerson  @AllocationsID,
													0,
													@pSchedulingPersonID,
													@pDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					END
					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

					SET @FromID = @AllocationsSPID;

				 END;

                SELECT @FromDutyName = AD_DutyName,
				       @FromDutyDate = ASP_DutyDate,
					   @FromMarkWIAD = CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END,
					   @FromMarkActual = CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END,
					   @SchedulingTeamId = AL_SchedulingTeamID,
				       @FromSPID =  ASP_SchedulingPersonID,
					   @FromDutyDate = ASP_DutyDate,
					   --@vweeknumber = weeknumber,
					   @FromDuration = AD_Duration,
					   @FromBreakDuration = AD_DutyBreakTime,
					   @FromIDay = ASP_iDay,
					   @FromIsHomeTeam = CASE WHEN ASP_AllocationsID = @pAllocationsID THEN 1 ELSE 0 END,
					   @FromDutyType = AD_DutyType,
					   @FromAllocationsDutyID = AD_AllocationsDutyID
                  FROM AllocationsScheduledPersons ASP
				 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				 INNER JOIN Allocations AL on AL_AllocationsID = ASP_AllocationsID
				 WHERE ASP_AllocationsSPID = @FromID

				IF  ( ISNULL(@FromIsHomeTeam,0) = 0 )
				BEGIN
				  THROW 51093, 'Cannot Mark WIAD/Actual as Scheduled Person is from a different team', 1;
				END				 
				 
				IF  ( @EditType = 'MARKWIAD' AND ISNULL(@pMarkWIAD,0) = 1 AND @FromDutyType NOT IN (7,9)
				       AND ISNULL(@FromMarkActual,0) = 0 )
				BEGIN
				  THROW 51093, 'Cannot Mark WIAD as duty already assigned for this day', 1;
				END

				IF  ( @EditType = 'MARKACTUAL' AND ISNULL(@pMarkActual,0) = 1 AND @FromDutyType NOT IN (7,9)
 				       AND ISNULL(@FromMarkWIAD,0) = 0 )
				BEGIN
				  THROW 51094, 'Cannot Mark Actual as duty already assigned for this day', 1;
				END

				IF  ( @EditType = 'MARKWIAD' AND ISNULL(@pMarkWIAD,0) = 0 AND @FromDutyType NOT IN (7,9) )
				BEGIN
				  THROW 51093, 'Cannot UnMark WIAD as duty already assigned for this day', 1;
				END

				IF  ( @EditType = 'MARKACTUAL' AND ISNULL(@pMarkActual,0) = 0 AND @FromDutyType NOT IN (7,9) )
				BEGIN
				  THROW 51094, 'Cannot UnMark Actual as duty already assigned for this day', 1;
				END

				IF NOT EXISTS ( SELECT TOP 1 ScheduledPersonID
								  FROM ScheduledPersonTeam_LINK
								 WHERE TeamID <> @SchedulingTeamId
								   AND ScheduledPersonID = @FromSPID
								   AND scheduledType = 1
								   AND @FromDutyDate between StartDate AND EndDate
                                   AND IsHomeTeam IN (0,2)
						  )
				BEGIN
				  IF (ISNULL(@pMarkWIAD,0) <> 0 OR ISNULL(@pMarkActual,0) <> 0 )
				   BEGIN
					THROW 51095, 'No additional team assigned for this Scheduled Person on this day', 1;
				   END
				END

				IF  ( @EditType = 'MARKWIAD' AND ISNULL(@pMarkWIAD,1) = 0 AND @FromMarkWIAD = 0 )
				BEGIN
				  THROW 51096, 'Cannot unmark this day as it is not marked as WIAD', 1;
				END

				IF  ( @EditType = 'MARKWIAD' AND ISNULL(@pMarkWIAD,0) = 1 AND @FromMarkWIAD = 1 )
				BEGIN
				  THROW 51097, 'This day is already marked as WIAD', 1;
				END

				IF  ( @EditType = 'MARKWIAD' AND ISNULL(@pMarkWIAD,0) = 1 AND ISNULL(@FromMarkActual,0) = 1 )
				BEGIN
				  SET @pMarkActual = NULL
				END

				IF  ( @EditType = 'MARKACTUAL' AND ISNULL(@pMarkActual,0) = 0 AND @FromMarkActual = 0 )
				BEGIN
				  THROW 51098, 'Cannot unmark this day as it is not marked as Actual', 1;
				END

				IF  ( @EditType = 'MARKACTUAL' AND ISNULL(@pMarkActual,0) = 1 AND ISNULL(@FromMarkActual,0) = 1 )
				BEGIN
				  THROW 51099, 'This day is already marked as Actual', 1;
				END

				IF  ( @EditType = 'MARKACTUAL' AND ISNULL(@pMarkActual,0) = 1 AND ISNULL(@FromMarkWIAD,0) = 1 )
				BEGIN
				  SET @pMarkWIAD = NULL
				END

					  UPDATE AllocationsScheduledPersons
						 SET ASP_WIADStatus  = CASE WHEN @pMarkWIAD IS NULL AND @pMarkActual = 0 THEN 0
													WHEN @pMarkWIAD IS NULL AND @pMarkActual = 1 THEN 2
													WHEN @pMarkActual IS NULL AND @pMarkWIAD = 0 THEN 0
													WHEN @pMarkActual IS NULL AND @pMarkWIAD = 1 THEN 1
													ELSE 0
													END,
							 ASP_DutyTeamID = CASE WHEN @FromDutyType IN (7,9 ) 
												   THEN NULL ELSE ASP_DutyTeamID END,
							 ASP_UpdatedBy = @vuserID,
							 ASP_UpdatedDate = getutcdate()
					   WHERE ASP_AllocationsSPID = @FromID

				SET @ToMarkWIAD =  case when @pMarkWIAD IS NULL AND @pMarkActual = 0 THEN 0
										when @pMarkWIAD IS NULL AND @pMarkActual = 1 THEN 2
										when @pMarkActual IS NULL AND @pMarkWIAD = 0 THEN 0
										when @pMarkActual IS NULL AND @pMarkWIAD = 1 THEN 1
										ELSE 0 
									ENd
				SET @FromDuration = @FromDuration - ISNULL(@FromBreakDuration,0)

				IF ( @FromMarkWIAD = 1 AND @ToMarkWIAD = 2 AND ISNULL(@FromDuration,0) > 0 )
				 BEGIN

					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,0,
										@FromDuration,0,0,@pNetLogin

				 END

				IF ( @FromMarkActual = 1 AND @ToMarkWIAD = 1 AND ISNULL(@FromDuration,0) > 0  )
				 BEGIN

					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,@FromDuration,0,
										0,0,@pNetLogin

				 END
						INSERT INTO history
									( historytype, attributeid, HistorySubType,
										datetime, userid, history )
							SELECT ht.id AS historytype,
									@FromID AS attributeid,
									'PH' AS HistorySubType,
									getdate(),
									@vuserID,
									CASE
										WHEN @EditType = 'MARKACTUAL'
										THEN
											CASE
												WHEN @pMarkActual = 1 THEN 'Marked Actual at '
												WHEN @pMarkActual = 0 THEN 'Unmarked Actual at '
											END
										ELSE
											CASE
												WHEN @pMarkWIAD = 1 THEN 'Marked WIAD at '
												WHEN @pMarkWIAD = 0 THEN 'Unmarked WIAD at '
											END
									END
									+ FORMAT(Getdate(),'HH:mm')
									+ ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')
									+ ' By ' + @vname
							FROM   HistoryTypes ht
							WHERE  historytype = 'AllocationScheduledPerson'

						EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,@AllocationsDutyID,
														 @AllocationsSPID,
														 0,
														 @pNetLogin;

						IF ( ISNULL(@ReturnValue,1) <> 0 )
						BEGIN
							THROW 51000, 'Error while creating Duty update status', 1;
						END

		   END

		------------------------------------------------------------
		-- Mark Duty as WIAD OR Absent
		------------------------------------------------------------

		IF  ( @EditType = 'ADDCOMMENTS' )
		  BEGIN

		     SET @PublishStatus = 0

		     IF ( ISNULL(@pAllocationsDutyID,0) > 0 )
			  BEGIN

				 SELECT @FromDutyType = AD_DutyType,
						@FromDutyName = AD_DutyName
				   FROM AllocationsDuties
				  WHERE AD_AllocationsDutyID = @pAllocationsDutyID

			  END

			 IF ( ( ISNULL(@pCommentType,9) in (0,1) AND  ISNULL(@pAllocationsDutyID,0) = 0  AND @pDutyComments IS NOT NULL)
					       OR ( ISNULL(@pCommentType,9) in (0,1) AND ISNULL(@FromDutyType,0) IN ( 7 , 8)  ) AND @pDutyComments IS NOT NULL )
			   BEGIN

					    SET @ToDutyType = CASE WHEN ISNULL(@FromDutyType,7) = 7 THEN 9 
											   WHEN @FromDutyType = 8 THEN 12
											   ELSE 9 END
						SET @IsCreateHistory = CASE WHEN @FromDutyType = 8 THEN 0 ELSE 1 END

						SET @FromDutyName = CASE WHEN @FromDutyName IS NULL THEN 'U' ELSE @FromDutyName END

						EXEC @ReturnValue = usp_CreateDuty	@pAllocationsID,
											@pAllocationsSPID,
											@pSchedulingPersonID,
											@FromDutyName,
											@pDutyDate,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											@pIsShiftleader,
											1,
											0,
											NULL,
											@pNetLogin,
											@ToDutyType,
											@IsCreateHistory = @IsCreateHistory,
											@AllocationsDutyID = @AllocationsDutyID OUTPUT

						IF ( ISNULL(@ReturnValue,0) > 0 )
						BEGIN
							THROW 51000, 'Error while creating duty', 1;
						END 

				 END

				 IF ( ISNULL(@FromDutyType,0) NOT IN ( 7,8) )
				  BEGIN
					SET @AllocationsDutyID = CASE WHEN ISNULL(@pAllocationsDutyID,0) > 0
												  THEN @pAllocationsDutyID
												  ELSE @AllocationsDutyID
												  END
				  END

				 IF (ISNULL(@pCommentType,9) in (0,1) )
				  BEGIN

					UPDATE AllocationsDuties
					   SET AD_Comments = @pDutyComments,
					       AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					 WHERE AD_AllocationsDutyID = @AllocationsDutyID

					INSERT INTO history ( historytype,
							  attributeid,
							  HistorySubType,
							  datetime,
							  userid,
							  history )
					SELECT ht.id AS historytype,
						   @AllocationsDutyID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   'Comments were added/changed by '
						   + @vname
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.<br><br>'
					FROM HistoryTypes HT (Nolock)
					WHERE  historytype = 'AllocationDuty'

				   END

		     IF ( ISNULL(@pAllocationsSPID,0) > 0 AND ISNULL(@pCommentType,9) in (0,2))
			  BEGIN

			    SET @AllocationsSPID = @pAllocationsSPID

			  END

			  IF ( ISNULL(@AllocationsDutyID,0) = 0 )
			   BEGIN
			    SET @AllocationsDutyID = CASE WHEN ISNULL(@pAllocationsDutyID,0) > 0
											  THEN @pAllocationsDutyID
											  ELSE @AllocationsDutyID
											  END
			   END

			  IF ( ISNULL(@pAllocationsSPID,0) = 0  AND ISNULL(@pCommentType,9) in (0,2)
					AND ISNULL(@pSchedulingpersonID,0) > 0 )
			   BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @pAllocationsID,
													@AllocationsDutyID,
													@pSchedulingpersonID,
													@pDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					END
					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

			   END

				 IF ( ISNULL(@pCommentType,9) in (0,2) AND ISNULL(@AllocationsSPID,0) > 0 )
				  BEGIN

					UPDATE AllocationsScheduledPersons
					   SET ASP_Comments = @pPersonComments,
						   ASP_UpdatedBy = @vuserID,
						   ASP_UpdatedDate = GETUTCDATE()
					 WHERE ASP_AllocationsSPID = @AllocationsSPID
					   AND ASP_AllocationsID =  @pAllocationsID

					SET @RowCount = @@ROWCOUNT

					IF ( ISNULL(@RowCount,0) > 0)
					 BEGIN
						INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )
						SELECT ht.id AS historytype,
							   @AllocationsSPID AS attributeid,
							   'PH' AS HistorySubType,
							   getdate(),
							   @vuserID,
							   'Comments were added/changed by '
							   + @vname
							   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.<br><br>'
						FROM HistoryTypes HT (Nolock)
						WHERE  historytype = 'AllocationScheduledPerson'
				     END
					ELSE
					 BEGIN

						UPDATE AP
						   SET AP.AAP_Comments = @pPersonComments,
							   AP.AAP_UpdatedBy = @vuserID,
							   AP.AAP_UpdatedDate = GETUTCDATE()
						  FROM AllocationsAddPersons AP
						 INNER JOIN AllocationsScheduledPersons ASP ON AP.AAP_SchedulingPersonID = ASP.ASP_SchedulingPersonID
																   AND AP.AAP_DutyDate = ASP.ASP_DutyDate
						 WHERE ASP.ASP_AllocationsSPID = @AllocationsSPID
						   AND AP.AAP_AllocationsID =  @pAllocationsID

						 SET @RowCount = @@ROWCOUNT

							IF ( ISNULL(@RowCount,0) > 0)
							 BEGIN

							    SELECT @AllocationsAPID = AP.AAP_AllocationsAPID
								  FROM AllocationsAddPersons AP
								 INNER JOIN AllocationsScheduledPersons ASP ON AP.AAP_SchedulingPersonID = ASP.ASP_SchedulingPersonID
																		   AND AP.AAP_DutyDate = ASP.ASP_DutyDate
								 WHERE ASP.ASP_AllocationsSPID = @AllocationsSPID
								   AND AP.AAP_AllocationsID =  @pAllocationsID

								EXEC @ReturnValue = usp_Create_HistoryAddPerson @AllocationsAPID

								INSERT INTO history ( historytype,
											attributeid,
											HistorySubType,
											datetime,
											userid,
											history )
								SELECT ht.id AS historytype,
										@AllocationsAPID AS attributeid,
										'PH' AS HistorySubType,
										getdate(),
										@vuserID,
										'Comments were added/changed by '
										+ @vname
										+ ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.<br><br>'
								FROM HistoryTypes HT (Nolock)
								WHERE  historytype = 'AllocationScheduledPersonAddnlTeam'

							 END

					 END

				   END

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @pAllocationsID, @AllocationsDutyID,
													 @AllocationsSPID,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END

		   END

		  IF ( @EditType = 'MARKFORATTENTION' )
		   BEGIN

		     SET @PublishStatus = 0

			 IF ( @pMarkForAttention IS NULL )
			  BEGIN
			   THROW 51000, 'Invalid value for Mark for Attention parameter.', 1;
			  END

		     IF ( ISNULL(@pAllocationsDutyID,0) > 0 )
			  BEGIN

				 SELECT @FromDutyType        = AD_DutyType,
						@FromDutyName		 = AD_DutyName
				   FROM AllocationsDuties
				  WHERE AD_AllocationsDutyID = @pAllocationsDutyID

			  END

			 IF ( ( ISNULL(@pAllocationsDutyID,0) = 0 AND @pMarkForAttention IS NOT NULL )
			      OR  ( ISNULL(@FromDutyType,0) IN ( 7, 8) ) )
			   BEGIN

					    SET @ToDutyType = CASE WHEN ISNULL(@FromDutyType,7) = 7 THEN 9 
											   WHEN @FromDutyType = 8 THEN 12
											   ELSE 9 END
						SET @IsCreateHistory = CASE WHEN @FromDutyType = 8 THEN 0 ELSE 1 END

						SET @FromDutyName = CASE WHEN @FromDutyName IS NULL THEN 'U' ELSE @FromDutyName END

						EXEC @ReturnValue = usp_CreateDuty	@pAllocationsID,
											@pAllocationsSPID,
											@pSchedulingPersonID,
											@FromDutyName,
											@pDutyDate,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											@pIsShiftleader,
											1,
											0,
											NULL,
											@pNetLogin,
											@ToDutyType,
											@pMarkForAttention,
											NULL,
											@IsCreateHistory = @IsCreateHistory,
											@AllocationsDutyID = @AllocationsDutyID OUTPUT

						IF ( ISNULL(@ReturnValue,0) > 0 )
						BEGIN
							THROW 51000, 'Error while creating duty', 1;
						END

				 END

		     IF ( ISNULL(@pAllocationsSPID,0) > 0 )
			  BEGIN

			    SET @AllocationsSPID = @pAllocationsSPID

			  END

			 IF ( ISNULL(@FromDutyType,0) NOT IN (7,8) )
			  BEGIN
			    SET @AllocationsDutyID = CASE WHEN ISNULL(@pAllocationsDutyID,0) > 0
											  THEN @pAllocationsDutyID
											  ELSE @AllocationsDutyID
											  END
			  END
			  			   			   
			  IF ( ISNULL(@pAllocationsSPID,0) = 0  AND @pMarkForAttention IS NOT NULL
					AND  ISNULL(@pSchedulingpersonID,0) > 0 )
			   BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @pAllocationsID,
													@AllocationsDutyID,
													@pSchedulingpersonID,
													@pDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT

					IF ( ISNULL(@ReturnValue,0) > 0 )
					  BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					  END ;

					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

			   END

					UPDATE AllocationsDuties
					   SET AD_isAttention = @pMarkForAttention,
					       AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					 WHERE AD_AllocationsDutyID = @AllocationsDutyID

					INSERT INTO history ( historytype,
							  attributeid,
							  HistorySubType,
							  datetime,
							  userid,
							  history )
					SELECT ht.id AS historytype,
						   @AllocationsDutyID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   CASE WHEN @pMarkForAttention = 1
								THEN 'Marked for Attention on ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + ' by '
								ELSE 'Unmarked for Attention on ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + ' by '
							END
						   + @vname
					FROM HistoryTypes HT (Nolock)
					WHERE  historytype = 'AllocationDuty'

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @pAllocationsID,@AllocationsDutyID,
													 @AllocationsSPID,
													 @PublishStatus,
													 @pNetLogin;



					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END ;

		   END;

		  IF ( @EditType = 'MARKPURPLE' )
		   BEGIN

		     SET @PublishStatus = 0

			 IF ( @pMarkPurple IS NULL )
			  BEGIN
			   THROW 51000, 'Invalid value for Mark for Purpul parameter.', 1;
			  END

		     IF ( ISNULL(@pAllocationsDutyID,0) > 0 )
			  BEGIN

				 SELECT @FromDutyType           = AD_DutyType
				   FROM AllocationsDuties
				  WHERE AD_AllocationsDutyID = @pAllocationsDutyID

			  END

			 IF ( ( ISNULL(@pAllocationsDutyID,0) = 0 AND @pMarkPurple IS NOT NULL )
			      OR  ( ISNULL(@FromDutyType,0) = 7 ) )
			   BEGIN

						EXEC @ReturnValue = usp_CreateDuty	@pAllocationsID,
											@pAllocationsSPID,
											@pSchedulingPersonID,
											'U',
											@pDutyDate,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											@pIsShiftleader,
											1,
											0,
											NULL,
											@pNetLogin,
											9,
											@pMarkForAttention,
											NULL,
											@AllocationsDutyID = @AllocationsDutyID OUTPUT


						IF ( ISNULL(@ReturnValue,0) > 0 )
						BEGIN
							THROW 51000, 'Error while creating duty', 1;
						END

				 END

		     IF ( ISNULL(@pAllocationsSPID,0) > 0 )
			  BEGIN

			    SET @AllocationsSPID = @pAllocationsSPID

			  END

			 IF ( ISNULL(@FromDutyType,0) <>  7 )
			  BEGIN
			    SET @AllocationsDutyID = CASE WHEN ISNULL(@pAllocationsDutyID,0) > 0
											  THEN @pAllocationsDutyID
											  ELSE @AllocationsDutyID
											  END
			  END

			  IF ( ISNULL(@pAllocationsSPID,0) = 0  AND @pMarkPurple IS NOT NULL )
			   BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @pAllocationsID,
													@AllocationsDutyID,
													@pSchedulingpersonID,
													@pDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51000, 'Error while creating scheduled person allocation', 1;
					END
					IF (@AllocationsSPID ) = 0
					  BEGIN
						THROW 51001, 'Error while creating allocation for scheduled person', 1;
					  END

			   END

					UPDATE AllocationsDuties
					   SET AD_isRequest = @pMarkPurple,
					       AD_UpdatedBy = @vuserID,
						   AD_UpdatedDate = GETUTCDATE()
					 WHERE AD_AllocationsDutyID = @AllocationsDutyID

					INSERT INTO history ( historytype,
							  attributeid,
							  HistorySubType,
							  datetime,
							  userid,
							  history )
					SELECT ht.id AS historytype,
						   @AllocationsDutyID AS attributeid,
						   'DH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   CASE WHEN @pMarkPurple =1
								THEN 'Marked as Purple Font on ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + ' by '
								ELSE 'Unmarked Purple Font on ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + ' by '
							END
						   + @vname
					FROM HistoryTypes HT (Nolock)
					WHERE  historytype = 'AllocationDuty'

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @pAllocationsID, @AllocationsDutyID,
													 @AllocationsSPID,
													 @PublishStatus,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END

		   END;

	   IF ( @EditType = 'MARKOVERTIME' )
		 BEGIN

		   	IF ( @pMarkOverTime IS NULL OR @pOverTimeHrs IS NULL OR @pAllocationsSPID IS NULL )
			  BEGIN
			   THROW 51000, 'Invalid value for Markovertime parameter.', 1;
			  END

			     SET @AllocationsSPID = @pAllocationsSPID

				 SELECT @FromMarkedOvertime     = ASP_MarkedOverTime,
						@FromOverTimeHrs		= ASP_OverTimeHours,
						@FromAllocationsDutyID  = AD_AllocationsDutyID,
						@FromMarkOverTweleve    = ASP_OverTwelveStatus,
						@FromSPID				= ASP_SchedulingPersonID,
						@FromDuration			= AD_Duration,
						@FromDutyDate			= ASP_DutyDate
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  WHERE ASP_AllocationsSPID = @AllocationsSPID;

				  SET @AllocationsDutyID = @FromAllocationsDutyID

				  IF ( @FromMarkedOvertime = 1 AND @pMarkOverTime = 0 )
				    SET @Message = 'Manual Overtime deleted on '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+' by '+ @vname

				  IF ( @FromMarkedOvertime = 1 AND @pMarkOverTime = 1 AND @FromOverTimeHrs <> @pOverTimeHrs )

				    SET @Message = 'Manual Overtime hours changed from '
									+ right('0'+CAST( isnull(@FromOverTimeHrs,0) / 3600 AS varchar(2)),2) + ':'
									+ right('0' + CAST( (isnull(@FromOverTimeHrs,0) % 3600)/60 AS varchar(2)),2)
									+' to '
									+ right('0'+CAST( isnull(@pOverTimeHrs,0) / 3600 AS varchar(2)),2) + ':'
									+ right('0' + CAST( (isnull(@pOverTimeHrs,0) % 3600)/60 AS varchar(2)),2)
									+' on '
									+FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
									+' by '
									+@vname

				  IF ( ISNULL(@FromMarkedOvertime,0) = 0 AND @pMarkOverTime = 1 )
				     SET @Message = 'Manual Overtime of '
									+ right('0'+CAST( isnull(@pOverTimeHrs,0) / 3600 AS varchar(2)),2) + ':'
									+ right('0' + CAST( (isnull(@pOverTimeHrs,0) % 3600)/60 AS varchar(2)),2)
									+' hours added on '
									+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
									+ ' by '+@vname

					UPDATE AllocationsScheduledPersons
					   SET ASP_MarkedOverTime = @pMarkOverTime,
					       ASP_OverTimeHours = @pOverTimeHrs,
						   ASP_UpdatedBy = @vuserID,
						   ASP_UpdatedDate = GETUTCDATE()
					 WHERE ASP_AllocationsSPID = @AllocationsSPID

					INSERT INTO history ( historytype,
							  attributeid,
							  HistorySubType,
							  datetime,
							  userid,
							  history )
					SELECT ht.id AS historytype,
						   @AllocationsSPID AS attributeid,
						   'PH' AS HistorySubType,
						   getdate(),
						   @vuserID,
						   @Message
					FROM HistoryTypes HT (Nolock)
					WHERE  historytype = 'AllocationScheduledPerson'

					SET @FromDuration = CASE WHEN @FromMarkWIAD = 1
											 THEN 0
											 ELSE ISNULL(@FromDuration,0) - ISNULL(@FromBreakDuration,0) END

					INSERT INTO @EditAllocationStatus
					EXEC usp_Update_DutyAccPeriodSummary @FromDutyDate, @FromSPID,
										@FromDuration,@FromDuration,
										@FromOverTimeHrs,@pOverTimeHrs,@pNetLogin

					select  @vErrorMsg = ErrorMsg , @vSPStatus=SPStatus
						from @EditAllocationStatus

					IF ( ISNULL(@vSPStatus,1) <> 0 )
					BEGIN
						THROW 51000, @vErrorMsg, 1;
					END;

					EXEC @ReturnValue = usp_CreateAllocationsUpdate @pAllocationsID,
													@AllocationsDutyID,
													 @AllocationsSPID,
													 0,
													 @pNetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					BEGIN
						THROW 51000, 'Error while creating Duty update status', 1;
					END

	      END

	    IF ( @@TRANCOUNT  > 0 )
         BEGIN
           COMMIT  TRANSACTION
         END

		IF ( @EditType NOT IN ('MARKLEAVE','PDL') )
		 BEGIN
			SELECT 0 as SPExecStatus,
					'Success' as SPMessage
		 END
		ELSE
		 BEGIN
		  RETURN 0
		 END

	  IF ( @StartDate IS NOT NULL AND @EndDate IS NOT NULL AND @IsReturnData = 1)
		EXEC usp_get_EditWeekly @StartDate,@EndDate,@SchedulingTeamID,@pNetLogin,@ScheduledpersonList

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

       IF ( ERROR_NUMBER() in (1204,1205,1222,3930) AND @EditType NOT IN ('MARKLEAVE','PDL') )
		 SELECT 1 SPExecStatus,'Somebody else is also editing this duty. Please try again' AS SPMessage
	   ELSE
	    BEGIN
	     IF ( @EditType NOT IN ('MARKLEAVE','PDL') )
	 	   SELECT ISNULL(@@IDENTITY,ERROR_NUMBER()) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage
		 ELSE
		  RETURN 1
		END

	END CATCH;

END