USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_EditYearly_Edits]    Script Date: 30/03/2026 14:12:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                         PROCEDURE [dbo].[usp_EditYearly_Edits]
@EditType 		     VARCHAR(30),
@pNetLogin           VARCHAR(30),
@StartDate           DATE,
@EndDate             DATE,
@TeamID              INT ,
@SchedulingPersonID  INT
AS
BEGIN

   -- Edit Type Parameters are given below.
   -- UNASSIGN           -> Unassign duty from a person
   -- DELETE             -> Delete duty
   -- APPLYROTA			 -> Apply ROTA to person



   SET NOCOUNT ON;
   SET DATEFORMAT YMD;


   DECLARE   @vname						VARCHAR(50),
			 @vuserID					INT,
			 @NewAllocationID			INT,
			 @FromIDay					INT,
			 @FromIsHomeTeam			INT,
			 @FromMarkWIAD				INT,
			 @FromMarkActual			INT,
			 @FromDutyTeamID			INT,
			 @FromDutyName				VARCHAR(100),
			 @FromDutyDate				DATE,
			 @FromSPID					INT,
			 @FromDuration				INT,
			 @FromMarkedOvertime		INT,
			 @FromStarttime				INT,
			 @FromEndTime				INT,
			 @FromIsFreelancer			INT = 0,
			 @FromLeaveStartTime		INT,
			 @FromLeaveEndTime			INT,
			 @FromAllocationsDutyID		INT,
			 @FromAllocationsID			INT,
			 @FromAllocationsSPID		INT,
			 @LeaveStartTimeLocal		DATETIME,
			 @LeaveEndTimeLocal			DATETIME,
			 @DutyType					INT,
			 @LeaveStatus				INT,
			 @ChargingStatus			INT,
			 @IsOverrideOver12			INT,
			 @Cntr						INT = 0,
			 @ReturnValue				INT;


   DECLARE   @SchedulingTeamId				INT,
			 @DutyTeamID					INT,
			 @FromWeekNumber				INT = 0,
			 @vIsShiftleader				INT = 0,
			 @return_value					INT,
			 @IsFreelancer					INT = 0,
			 @HomeTeamID					INT,
			 @SkipRecordFlag				BIT = 0,
			 @vErrorMsg						VARCHAR(1000),
             @vSPStatus						INT,
			 @MarkedOvertime				INT,
			 @IsRestrictDeleteDuty			BIT,
			 @IsRestrictDeleteDutyAddTeam	BIT,
			 @AdditionalTeamDuty			BIT,
			 @IsNeedCovering				BIT,
			 @IsActive						BIT,
			 @MinWeekNumber					INT,
			 @MaxWeekNumber					INT,
			 @RestrictApplyROTAPattern		BIT,
			 @RPStartDate					DATE,
			 @RPEndDate						DATE,
			 @HistoryTypeDuty				INT,
			 @HistoryTypeJob				INT,
			 @SetMarkAttention				BIT = 0,
			 @ROTAStartTime					INT,
			 @ROTAEndTime					INT,
			 @ROTAStartTimeLocal			DATETIME,
			 @ROTAEndTimeLocal				DATETIME,
			 @schedulingTeamName			VARCHAR(120),
			 @SkippedDueToNoROTA			BIT = 0,
			 @HistoryTypePerson				INT,
			 @WeekNumber					INT,
			 @DisplayName					NVARCHAR(100),
			 @NewJobID						INT,
			 @NewDutyID						INT;

    DECLARE  @TempRota TABLE (  WeekNumber INT,
								iDay INT,
								DutyName NVARCHAR(100),
								Duration FLOAT,
								StartTime FLOAT,
								EndTime FLOAT,
								BackColour BIGINT,
								FontColour BIGINT,
								StartDate DATETIME,
								EndDate DATETIME,
								SortCode NVARCHAR(30),
								aftermidnight INT,
								dutyProgramId INT,
								dutyProgramId2 INT,
								dutyProgramId3 INT,
								dutyProgramId4 INT,
								dutyProgramId5 INT,
								dutyProgramId6 INT,
								dutyBreakTime INT,
								dutyColorId INT,
								MasterDutyId INT,
								IsNeedCovering BIT,
								DutyTypeID INT,
								IsOverrideOver12 BIT,
								SchedulingTeamId INT,
							    IsHomeTeam INT,
								DutyDate DATE)

   DECLARE @TempAllocation TABLE ( AllocationsDutyID INT, DutyName NVARCHAR(100))
   DECLARE @TempJob TABLE ( AllocateJobID INT)

   DECLARE  @TempHistory	TABLE (HistoryID INT,
							       AttributeID INT,
								   HistorySubType VARCHAR(10),
								   HistoryType INT,
								   UserID INT,
								   History NVARCHAR(MAX),
								   CreateDateTime datetime)

   DECLARE  @TempAllocations TABLE( AllocationsDutyID INT,
									AllocationsID INT,
									AllocationsSPID	INT,
									iday INT,
									IsHomeTeam INT,
									MarkWIAD INT,
									MarkActual INT,
									DutyName NVARCHAR(100),
									SchedulingTeamID INT,
									DutyTeamID INT,
									Duration INT,
									SchedulingPersonID INT,
									WeekNumber INT,
									DutyDate DATE,
									LeaveStartTime INT,
									LeaveEndTime INT,
									MarkedOvertime INT,
									StartTime INT,
									EndTime INT,
									IsNeedCovering INT,
									DutyType INT,
									LeaveStatus INT,
									ChargingStatus INT,
									MarkOverTwelve INT,
									LeaveType INT,
									LeaveStartTimeLocal DATETIME,
									LeaveEndTimeLocal DATETIME,
									RecordAction VARCHAR(1),
									DutyStatus INT,
									ROTACreationStatus INT)


      SELECT  @vname =  UD_DisplayName,
			  @vuserID = UD_UserID
	    FROM UserDetails
	   WHERE UD_NetLogin = @pNetLogin

      SELECT @DisplayName = UD_DisplayName
	    FROM UserDetails
	   WHERE UD_UserID = @SchedulingPersonID

	  SELECT @HistoryTypeDuty =id
		FROM HistoryTypes
	   WHERE historytype = 'AllocationDuty'

	  SELECT @HistoryTypePerson =id
		FROM HistoryTypes
	   WHERE historytype = 'AllocationScheduledPerson'

    IF  (@EditType = 'APPLYROTA' )
 	 BEGIN

	  SELECT @HistoryTypeJob =id
		FROM historytypes ht
	   WHERE historytype='AllocationJobs'

	 END

	    select @IsRestrictDeleteDuty = ISNULL(IsRestrictDeleteDuty,1),
		       @RestrictApplyROTAPattern = ISNULL(RestrictApplyROTAPattern,0),
			   @schedulingTeamName = schedulingTeamName
		  from schedulingTeams
		 where schedulingTeamId = @TeamID

    BEGIN TRY
        BEGIN TRANSACTION

		------------------------------------------------
		-- Unassign Duties from allocated to Unallocated
		------------------------------------------------

           IF  (@EditType = 'APPLYROTA' )
 		    BEGIN

			    SELECT @MinWeekNumber = MIN(ixYearWeek),
				       @MaxWeekNumber = MAX(ixYearWeek)
				  FROM TimeDimension
				 WHERE dDateTime between @StartDate and @EndDate


                 SELECT @RPStartDate = MIN(rp.StartDate),
						@RPEndDate = MAX(rp.EndDate)
				   FROM RotaPeople rp
				  INNER JOIN MasterRotas AS mr ON mr.rotaid = rp.rotaid
				  WHERE rp.ScheduledPersonID = @SchedulingPersonID
					AND mr.teamid = @teamId
					AND rp.isactive = 1

			   IF ( @StartDate IS NULL)
					THROW 51025,'There is no Rota mapped to this person. Please map a Rota Pattern in Forward Planning and try again' , 1;

			   IF ( @StartDate < @RPStartDate OR @EndDate > @RPEndDate)
			    BEGIN

				 SET @vErrorMsg = ''

				 IF ( @StartDate < @RPStartDate )
				  SET @vErrorMsg = FORMAT(@StartDate,'dd/MM/yyyy')+'-'+
				                 FORMAT(DATEADD(Day,-1,@RPStartDate),'dd/MM/yyyy')

				 IF ( @EndDate > @RPEndDate )
				  SET @vErrorMsg = @vErrorMsg +' '+FORMAT(DATEADD(Day,1,@RPEndDate),'dd/MM/yyyy')+'-'+
				                 FORMAT(@EndDate,'dd/MM/yyyy')

				  SET @vErrorMsg = 'There is no Rota mapped to this person from '+@vErrorMsg
				                 +'. Please map a Rota Pattern in Forward Planning and try again.';

				  THROW 51026,@vErrorMsg , 1;

			    END

			    BEGIN

					 INSERT INTO @TempRota
					    SELECT RT.WeekNumber,
							   RT.iDay,
							   RT.DutyName,
							   RT.Duration,
							   RT.StartTime,
							   RT.EndTime,
							   RT.BackColour,
							   RT.FontColour,
							   RT.StartDate,
							   RT.EndDate,
							   RT.SortCode,
							   0,
							   RT.DutyProgramId,
							   RT.DutyProgramId2,
							   RT.DutyProgramId3,
							   RT.DutyProgramId3,
							   RT.DutyProgramId5,
							   RT.DutyProgramId6,
							   RT.DutyBreakTime,
							   RT.DutyColourID,
							   RT.MasterDutyID,
							   RT.IsNeedCovering,
							   RT.IsOverrideOver12,
							   RT.DutyTypeID,
							   RT.SchedulingTeamID,
							   RT.isHomeTeam,
							   RT.DutyDate
						  FROM dbo.ufn_get_SchedulingTeamROTAD_ForDATE(@StartDate,@EndDate,@TeamID,@SchedulingPersonID) RT

			    IF NOT EXISTS ( SELECT 1 FROM @TempRota )
					THROW 51027,'There is no Rota mapped to this person. Please map a Rota Pattern in Forward Planning and try again' , 1;

				IF ( EXISTS ( SELECT 1
				                FROM @TempRota
				               WHERE IsNeedCovering = 1
						   ) AND @RestrictApplyROTAPattern = 1 )
				 BEGIN

				  THROW 51028, 'ROTA cannot be applied as there are duties which need covering', 1;

				 END
			 END

			    DECLARE @AllocationsDutyID	INT,
						@IsHomeTeam			INT,
						@AllocationsID		INT,
						@AllocationsSPID	INT;

		  		DECLARE CUR_NewSP CURSOR FOR
				SELECT * FROM
				( SELECT SL.ScheduledPersonID as SchedulingPersonID,
						 TD.dDateTime  as  DutyDate,
						 ASP_AllocationsSPID,
						 AL_AllocationsID,
						 ASP_AllocationsDutyID,
						 SL.IsHomeTeam
				 FROM Allocations AL
				INNER JOIN TimeDimension TD on TD.ixYearWeek = AL_WeekNumber
				INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = AL_SchedulingTeamID
				INNER JOIN @TempRota TR on TR.DutyDate = TD.dDateTime
				 LEFT JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID
											  AND SL.ScheduledPersonID = ASP_SchedulingPersonID
											  AND TD.ixDayInWeek = ASP_iDay
				WHERE TD.dDateTime BETWEEN SL.StartDate and SL.EndDate
				  AND TD.dDateTime BETWEEN @StartDate and @EndDate
				  AND AL_SchedulingTeamID = @TeamID
				  AND SL.ScheduledPersonID = @SchedulingPersonID
				  AND SL.scheduledType = 1
				  AND SL.IsHomeTeam = 1
				) FD WHERE ASP_AllocationsSPID IS NULL

				OPEN CUR_NewSP

	            FETCH NEXT FROM CUR_NewSP INTO  @FromSPID,
												@FromDutyDate,
												@FromAllocationsSPID,
												@AllocationsID,
												@AllocationsDutyID,
												@IshomeTeam;

		    WHILE @@FETCH_STATUS = 0
			 BEGIN
		  	  IF ( ISNULL(@FromAllocationsSPID,0) = 0 AND @SchedulingPersonID > 0 )
				BEGIN
				  IF ( @IshomeTeam <> 1 )
				    SET @IsFreelancer = dbo.ufn_IsFreeLancer(@FromSPID,@FromDutyDate)

				  IF ( @IshomeTeam IN (0,2) AND ISNULL(@IsFreelancer,0) = 0 )
				   BEGIN
				     THROW 51029, 'It is not possible to swap a duty with one belonging to another team ', 1;
				   END

				  IF ( ( @IshomeTeam = 1 OR  ISNULL(@IsFreelancer,0) = 1) AND @AllocationsID > 0 )
				   BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @AllocationsID,
													0,
													@SchedulingPersonID,
													@FromDutyDate,
													@pNetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT;


					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
						THROW 51030, 'Error in the execution of usp_CreateScheduledPerson', 1;
					END
				  END

				 END;

	            FETCH NEXT FROM CUR_NewSP INTO  @FromSPID,
												@FromDutyDate,
												@FromAllocationsSPID,
												@AllocationsID,
												@AllocationsDutyID,
												@IshomeTeam;

			   END

			   CLOSE CUR_NewSP;

			   DEALLOCATE CUR_NewSP;

		END

     IF  (@EditType IN ('UNASSIGN','DELETE','APPLYROTA') )
 	  BEGIN

		   IF (@StartDate IS NULL OR @EndDate IS NULL OR @TeamID IS NULL )
			BEGIN
			  THROW 51031, 'Incorrect parameters to the stored procedure', 1;
			END

			 INSERT @TempAllocations
             SELECT AD_AllocationsDutyID,
					AL_AllocationsID,
					ASP_AllocationsSPID,
					ASP_iDay,
					CASE WHEN ISNULL(ASP_DutyTeamID,0) <> al1.AL_SchedulingTeamID
						 THEN 0 ELSE 1
						 END as IsHomeTeam,
					CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END MarkWIAD,
					CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END MarkActual,
					AD_DutyName,
					AL_SchedulingTeamID,
					ASP_DutyTeamID,
					AD_Duration,
					ASP_SchedulingPersonID,
					AL_WeekNumber,
					AD_DutyDate,
					ASP_LeaveStartTimeSec,
					ASP_LeaveEndTimeSec,
					ASP_MarkedOverTime,
					AD_StartTimeSec,
					AD_EndTimeSec,
					AD_IsNeedCovering,
					AD_DutyType,
					ASP_LeaveStatus,
					ASP_ChargingStatus,
					ASP_OverTwelveStatus,
					ASP_LeaveType,
					ASP_LeaveStartTimeLocal,
					ASP_LeaveEndTimeLocal,
					NULL as RecordAction,
					1,
					0
			  FROM Allocations AL1
			 INNER JOIN TimeDimension TD on TD.ixYearWeek = AL_WeekNumber
			 INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsID = AL_AllocationsID
													AND TD.ixDayInWeek = ASP_iDay
			 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			 INNER JOIN UserDetails UD on UD_UserID = ASP_SchedulingPersonID
			 WHERE TD.dDateTime BETWEEN @StartDate and @EndDate
			   AND AL1.AL_SchedulingTeamID = @TeamID
			   AND UD_UserID = @SchedulingPersonID


			DECLARE CUR_AllocList
			 CURSOR FOR
             SELECT AllocationsDutyID,
			        AllocationsID,
					AllocationsSPID,
					iday,
					al.IsHomeTeam,
					al.MarkWIAD,
					al.MarkActual,
					al.DutyName,
					al.SchedulingTeamID,
					al.DutyTeamID,
					AL.Duration,
					AL.SchedulingPersonID,
					AL.WeekNumber,
					AL.DutyDate,
					AL.LeaveStartTime,
					AL.LeaveEndTime,
					AL.MarkedOvertime,
					AL.StartTime,
					AL.EndTime,
					AL.IsNeedCovering,
					DutyType,
					LeaveStatus,
					ChargingStatus,
					LeaveStartTimeLocal,
					LeaveEndTimeLocal
			  FROM @TempAllocations al
			 WHERE DutyType NOT IN ( 8,11,12)

			OPEN CUR_AllocList

		    FETCH NEXT FROM CUR_AllocList INTO  @FromAllocationsDutyID,
												@FromAllocationsID,
												@FromAllocationsSPID,
												@FromIDay,
												@FromIsHomeTeam,
												@FromMarkWIAD,
												@FromMarkActual,
												@FromDutyName,
												@SchedulingTeamID,
												@DutyTeamID,
												@FromDuration,
												@FromSPID,
												@FromWeekNumber,
												@FromDutyDate,
												@FromLeaveStartTime,
												@FromLeaveEndTime,
												@MarkedOvertime,
												@FromStartTime,
												@FromEndTime,
												@IsNeedCovering,
												@DutyType,
												@LeaveStatus,
												@ChargingStatus,
												@LeaveStartTimeLocal,
												@LeaveEndTimeLocal


		   WHILE @@FETCH_STATUS = 0
			 BEGIN

			    SET @SkipRecordFlag = 0
				SET @AdditionalTeamDuty = 0
				SET @IsRestrictDeleteDutyAddTeam = 0
				SET @SkippedDueToNoROTA = 0

			    IF ( ISNULL(@DutyTeamID,0) <> 0 AND ISNULL(@DutyTeamID,0) <>  @SchedulingTeamID AND @SkipRecordFlag = 0  )
				 BEGIN

				  IF EXISTS (  SELECT  1
								 from ScheduledPersonTeam_LINK sl
								inner join UserRoles ul on ul.UR_SchedulingTeamID = sl.TeamID
								inner join ref_roles rl on rl.RoleID = ul.UR_RoleID
								where @FromDutyDate between sl.StartDate and sl.EndDate
								  and @FromDutyDate between ul.UR_StartDate and ul.UR_EndDate
								  and ul.UR_UserID = @vuserID
								  and sl.TeamID = @DutyTeamID
								  and sl.scheduledType = 0
								  and rl.RoleName in  ( 'Scheduling Team Admin','Senior Scheduler',
								                        'Scheduler','Team Leader')
							)
				    BEGIN

					 SET @AdditionalTeamDuty = 1

					 SELECT @IsRestrictDeleteDutyAddTeam = ISNULL(IsRestrictDeleteDuty,1)
					   FROM schedulingTeams
					  WHERE schedulingTeamId = @DutyTeamID

					 IF ( @EditType = 'APPLYROTA')
					  BEGIN
					   SET @SetMarkAttention = 1
					  END

					   UPDATE @TempAllocations
					      SET RecordAction = 'A'
						WHERE AllocationsSPID = @FromAllocationsSPID

					END
				 ELSE
					BEGIN
					 SET @SkipRecordFlag = 1
					END

				END

				IF ( @EditType <> 'APPLYROTA' AND @DutyType IN (7,9) )
				  BEGIN
				   SET @SkipRecordFlag = 1
				  END

			   IF  ( @EditType = 'APPLYROTA' )
 				BEGIN

				 IF NOT EXISTS ( SELECT 1
				                   FROM @TempRota
								  WHERE WeekNumber = @FromWeekNumber
								    AND iDay = @FromIDay)
				  BEGIN
				   SET @SkipRecordFlag = 1
				   SET @SkippedDueToNoROTA = 1
				  END

				END;

                IF (   ( ISNULL(@FromLeaveStartTime,0) > 0 OR ISNULL(@FromLeaveEndTime,0) > 0 )
							AND @EditType <> 'APPLYROTA' AND ISNULL(@LeaveStatus,0) = 1 )
				 BEGIN
				   SET @SkipRecordFlag = 1
				 END

				IF (   ( ISNULL(@FromLeaveStartTime,0) > 0 OR ISNULL(@FromLeaveEndTime,0) > 0 )
							AND @EditType = 'APPLYROTA' AND ISNULL(@LeaveStatus,0) = 1 )
				 BEGIN

				  SELECT @ROTAStartTimeLocal = StartDate,
				         @ROTAEndTimeLocal = EndDate
				    FROM @TempRota
				   WHERE WeekNumber = @FromWeekNumber
				     AND iday = @FromIDay

				   IF (  ISNULL(@LeaveStartTimeLocal,0) < ISNULL(@ROTAStartTimeLocal,0)
					    OR ISNULL(@LeaveEndTimeLocal,0) > ISNULL(@ROTAEndTimeLocal,0) )
				    BEGIN
				     SET @SkipRecordFlag = 1
					END

				 END;

                IF (    ISNULL(@MarkedOvertime,0) = 1 AND @SkipRecordFlag = 0 )
				 BEGIN
				   SET @SkipRecordFlag = 1
				 END;

				IF ( ISNULL(@ChargingStatus,0) > 0 )
				BEGIN
					SET @SkipRecordFlag = 1
				END

				IF ( @SkipRecordFlag = 0  )
				 BEGIN
				   SET @IsFreelancer = dbo.ufn_IsFreeLancer(@SchedulingPersonID,@FromDutyDate)
				 END

			    IF ( @IsFreelancer = 0
				      AND ( ISNULL(@FromIsHomeTeam,1) = 0
					   and ( ISNULL(@FromMarkWIAD,0) = 0 AND ISNULL(@FromMarkActual,0) = 0) )  AND @SkipRecordFlag = 0  )
				BEGIN
				  SET @SkipRecordFlag = 1
				END

				IF ( @EditType = 'DELETE' )
				 SET @IsActive = 0
				ELSE
				 SET @IsActive = 1


				IF ( @EditType = 'DELETE' AND  @IsRestrictDeleteDuty = 1 AND @AdditionalTeamDuty = 0 AND @SkipRecordFlag = 0 )
				 BEGIN

				  SET @SkipRecordFlag = CASE WHEN SUBSTRING(@FromDutyName,2,1) in ('7','8','9')
											 THEN 0
											 WHEN ISNULL(@IsNeedCovering,1) in (0,1)
											 THEN 0
											 WHEN ISNULL(@FromStarttime,0) = 0 AND ISNULL(@FromEndTime,0) = 0
											 THEN 0
											 ELSE 1
									     END

                  SET @IsActive = CASE WHEN SUBSTRING(@FromDutyName,2,1) in ('7','8','9')
											 THEN 0
											 WHEN ISNULL(@FromStarttime,0) = 0 AND ISNULL(@FromEndTime,0) = 0
											 THEN 0
											 WHEN ISNULL(@IsNeedCovering,1) in (1)
											 THEN 1
											 WHEN ISNULL(@IsNeedCovering,1) in (0)
											 THEN 0
											 ELSE 0
									     END

				 END

				IF ( @EditType = 'DELETE' AND  @IsRestrictDeleteDutyAddTeam = 1 AND @AdditionalTeamDuty = 1 AND @SkipRecordFlag = 0 )
				 BEGIN

				  SET @SkipRecordFlag = CASE WHEN SUBSTRING(@FromDutyName,2,1) in ('7','8','9')
											 THEN 0
											 WHEN ISNULL(@IsNeedCovering,1) in (0,1)
											 THEN 0
											 WHEN ISNULL(@FromStarttime,0) = 0 AND ISNULL(@FromEndTime,0) = 0
											 THEN 0
											 ELSE 1
									     END

                  SET @IsActive = CASE WHEN SUBSTRING(@FromDutyName,2,1) in ('7','8','9')
											 THEN 0
											 WHEN ISNULL(@FromStarttime,0) = 0 AND ISNULL(@FromEndTime,0) = 0
											 THEN 0
											 WHEN ISNULL(@IsNeedCovering,1) in (1)
											 THEN 1
											 WHEN ISNULL(@IsNeedCovering,1) in (0)
											 THEN 0
											 ELSE 0
									     END

				 END

				IF ( @EditType = 'UNASSIGN' AND @SkipRecordFlag = 0 )
				 BEGIN

                  SET @IsActive = CASE WHEN ISNULL(@IsNeedCovering,1) = 0
											 THEN 0
									   ELSE 1
								   END

				 END

                IF ( @SkipRecordFlag = 1 AND @EditType = 'APPLYROTA' AND @SkippedDueToNoROTA = 0)
				 BEGIN

				   update @TempAllocations
				      SET RecordAction = 'S'
					WHERE AllocationsSPID = @FromAllocationsSPID

					SET @SetMarkAttention = 1

				 END

                IF ( ISNULL(@FromDuration,0) > 0 and @SkipRecordFlag = 0 )
				 BEGIN

					 UPDATE @TempAllocations
						SET RecordAction ='U',
							DutyStatus = CASE WHEN @IsActive = 0 THEN 9 ELSE 0 END
					  WHERE AllocationsSPID = @FromAllocationsSPID

	  			  INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
							  CreateDateTime, userid, history
							)
					SELECT @HistoryTypeDuty AS historytype,
						   AllocationsDutyID AS attributeid,
						   'DH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified '
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', UnAllocated Duty ( '
						   + AL.dutyname
						   + ' ) from '+ @DisplayName
					FROM   @TempAllocations AL
					where AllocationsSPID = @FromAllocationsSPID

	  			 /* INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
							  CreateDateTime, userid, history
							)
					SELECT @HistoryTypePerson AS historytype,
						   AllocationsSPID AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified '
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', UnAllocated Duty ( '
						   + AL.dutyname
						   + ' ) from '+ @DisplayName
					FROM   @TempAllocations AL
					where AllocationsSPID = @FromAllocationsSPID */


						INSERT INTO @TempHistory ( historytype,
								  attributeid,
								  HistorySubType,
								  CreateDateTime,
								  userid,
								  history )
						SELECT @HistoryTypeDuty AS historytype,
							   AllocationsSPID AS attributeid,
							   'PH' AS HistorySubType,
							   getdate(),
							   @vuserID,
							  'Over12 was Removed by system '+
							   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.'
						  FROM @TempAllocations AL
						 WHERE MarkOverTwelve in (1,2)
						   AND AllocationsSPID =  @FromAllocationsSPID

                END

				If ( @SkipRecordFlag = 0 )
				 BEGIN

	  			  INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
							  CreateDateTime, userid, history
							)
					SELECT @HistoryTypePerson AS historytype,
						   @FromAllocationsSPID AS attributeid,
						   'PH' as HistorySubType,
						   getdate(),
						   @vuserID,
						   'Modified '
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						   + ' By ' + @vname + ', UnAllocated Duty ( '
						   + AL.dutyname+' )'
					FROM   @TempAllocations AL
					WHERE AllocationsSPID = @FromAllocationsSPID

					IF ( @EditType IN ('UNASSIGN','DELETE') )
					 BEGIN

					  UPDATE AL
						 SET al.RecordAction = 'U',
							 al.DutyStatus = CASE WHEN ISNULL(@IsActive,1) = 0 THEN 9 ELSE 0 END
					   FROM  @TempAllocations AL
					   WHERE AllocationsSPID = @FromAllocationsSPID
					 END

					IF ( @EditType = 'APPLYROTA' AND @SkippedDueToNoROTA = 0 )
					 BEGIN

					    INSERT INTO AllocationsDuties
								  ( AD_AllocationsID,
									AD_DutyName,
									AD_Duration,
									AD_PlannedDuration,
									AD_iDay,
									AD_StartTimeSec,
									AD_EndTimeSec,
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
									AD_CreatedBy,
									AD_CreatedDate		)
						    SELECT	@FromAllocationsID,
									DutyName,
									Duration,
									Duration,
									iDay,
									StartTime,
									EndTime,
									DutyDate,
									StartDate,
									EndDate,
									StartDate,
									EndDate,
									dutyBreakTime,
									MasterDutyId,
									DutyTypeID,
									1,
									dutyColorId,
									dutyBreakTime,
									IsNeedCovering,
									IsOverrideOver12,
									0,
									dutyProgramId,
									dutyProgramId2,
									dutyProgramId3,
									dutyProgramId4,
									dutyProgramId5,
									dutyProgramId6,
									@vuserID,
									GETUTCDATE()
							  FROM @TempRota
							 WHERE DutyDate = @FromDutyDate

						    SET @FromAllocationsDutyID = @@IDENTITY

							 SELECT @IsOverrideOver12 = IsOverrideOver12
							   FROM @TempRota
							 WHERE DutyDate = @FromDutyDate

					 -- Create Duty here from ROTA

						UPDATE AllocationsScheduledPersons
						   SET  ASP_AllocationsDutyID = @FromAllocationsDutyID,
								ASP_OverTwelveStatus = case when @FromDuration > 43200
														and isnull(@IsOverrideOver12,1) = 1
														then 1 else 0 end,
								ASP_OverTwelveHrs = 0,
								ASP_IsOverseasOverTwelve = 0,
								ASP_UpdatedBy = @vuserID,
								ASP_UpdatedDate = GETUTCDATE()
						WHERE ASP_AllocationsSPID = @FromAllocationsSPID

						UPDATE @TempAllocations
						   SET DutyStatus = 0,
							   ROTACreationStatus = 1
						 WHERE AllocationsSPID = @FromAllocationsSPID


					  INSERT INTO @TempHistory
							(
							  historytype,
							  attributeid,
							  userid,
							  history,
							  HistorySubType,
							  CreateDateTime
							)
					  SELECT @HistoryTypePerson AS historytype,
						    @FromAllocationsSPID AS attributeid,
						   @vuserID,
						   'Created '
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
						   + FORMAT(Getdate(),'HH:mm')
						   + ' By ' + @vname
						   + ' assigned to '+@DisplayName
						   + '. Duty: ' + dutyname
						   +'.',
						   'PH',
						   GETDATE()
					  FROM   @TempAllocations AL
					  WHERE  AllocationsSPID = @FromAllocationsSPID

					  INSERT INTO @TempHistory
							(
							  historytype,
							  attributeid,
							  userid,
							  history,
							  HistorySubType,
							  CreateDateTime
							)
					  SELECT @HistoryTypeDuty AS historytype,
						    @FromAllocationsDutyID AS attributeid,
						   @vuserID,
						   'Created '
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
						   + FORMAT(Getdate(),'HH:mm')
						   + ' By ' + @vname
						   + ' assigned to '+@DisplayName
						   + '. Duty: ' + dutyname
						   +'.',
						   'PH',
						   GETDATE()
					  FROM   @TempAllocations AL
					  WHERE  AllocationsSPID = @FromAllocationsSPID

					-- Create Allocation history End

	                 -- Create Job here from ROTA

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
											 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.EndTime - 86400 )
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
									  and MJ.TeamID = @TeamID
									  and AD_AllocationsDutyID = @FromAllocationsDutyID

						-- Create Job history Start

					SET @NewJobID = @@IDENTITY

					INSERT INTO @TempHistory
						  (
								historytype,
								attributeid,
								userid,
								history,
								CreateDateTime
						  )
					SELECT @HistoryTypeJob AS historytype,
						   @NewJobID,
						   @vuserID,
						   'New Job created by '+@vname+' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +'.',
						   GETDATE()

				 END
			END

		    FETCH NEXT FROM CUR_AllocList INTO  @FromAllocationsDutyID,
												@FromAllocationsID,
												@FromAllocationsSPID,
												@FromIDay,
												@FromIsHomeTeam,
												@FromMarkWIAD,
												@FromMarkActual,
												@FromDutyName,
												@SchedulingTeamID,
												@DutyTeamID,
												@FromDuration,
												@FromSPID,
												@FromWeekNumber,
												@FromDutyDate,
												@FromLeaveStartTime,
												@FromLeaveEndTime,
												@MarkedOvertime,
												@FromStartTime,
												@FromEndTime,
												@IsNeedCovering,
												@DutyType,
												@LeaveStatus,
												@ChargingStatus,
												@LeaveStartTimeLocal,
												@LeaveEndTimeLocal

        END;

      CLOSE CUR_AllocList;

      DEALLOCATE CUR_AllocList;


                IF ( @EditType = 'APPLYROTA' AND @SetMarkAttention = 1 )
				 BEGIN

				  -- Create duty as unassigned with proper status

					    INSERT INTO AllocationsDuties
								  ( AD_AllocationsID,
									AD_DutyName,
									AD_Duration,
									AD_PlannedDuration,
									AD_iDay,
									AD_StartTimeSec,
									AD_EndTimeSec,
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
									AD_CreatedBy,
									AD_CreatedDate	)
							OUTPUT INSERTED.AD_AllocationsDutyID,
								   INSERTED.AD_DutyName
							  INTO @TempAllocation
						    SELECT	@FromAllocationsID,
									TR.DutyName,
									TR.Duration,
									TR.Duration,
									TR.iDay,
									TR.StartTime,
									TR.EndTime,
									TR.DutyDate,
									TR.StartDate,
									TR.EndDate,
									TR.StartDate,
									TR.EndDate,
									TR.dutyBreakTime,
									TR.MasterDutyId,
									TR.DutyTypeID,
									CASE WHEN ISNULL(tr.IsNeedCovering,1) = 1
										 THEN 1
										 ELSE 9 END,
									TR.dutyColorId,
									TR.dutyBreakTime,
									TR.IsNeedCovering,
									TR.IsOverrideOver12,
									0,
									TR.dutyProgramId,
									TR.dutyProgramId2,
									TR.dutyProgramId3,
									TR.dutyProgramId4,
									TR.dutyProgramId5,
									TR.dutyProgramId6,
									@vuserID,
									GETUTCDATE()
							   FROM @TempRota TR
							  INNER JOIN @TempAllocations TL on TR.WeekNumber = TL.weeknumber
														   and TR.iDay = TL.iday
							  WHERE RecordAction ='S'

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
								   TL.AllocationsDutyID AS attributeid,
								   getdate(),
								   @vuserID,
								   'Duty created '
								   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
								   + FORMAT(Getdate(),'HH:mm')
								   + ' By ' + @vname
								   + ' in unallocated section. Duty: '
								   + DutyName
								   +'.',
								   'DH'
							  FROM @TempAllocation TL
							  INNER JOIN HistoryTypes HT ON 1 = 1
							  WHERE  historytype = 'AllocationDuty'

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
									OUTPUT INSERTED.AJ_AllocateJobID
									  INTO @TempJob
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
											 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.starttime - 86400  )
											 ELSE dbo.ufn_ConvertToDateTime(AD_DutyDate,MJ.starttime ) END AS StartDate,
										   CASE
											 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AD.AD_DutyDate)
											 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.EndTime - 86400 )
											 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AD.AD_DutyDate)
											 ELSE dbo.ufn_ConvertToDateTime(AD.AD_DutyDate,MJ.EndTime) END AS EndDate,
										   CASE
											 WHEN MJ.starttime = 0 THEN AD.AD_DutyDate
											 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AD.AD_DutyDate)
											 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.starttime - 86400  )
											 ELSE dbo.ufn_ConvertToDateTime(AD_DutyDate,MJ.starttime ) END AS StartDateLocal,
											CASE
											 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AD.AD_DutyDate)
											 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD.AD_DutyDate),MJ.EndTime - 86400 )
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
									  INNER JOIN @TempAllocation TL on TL.AllocationsDutyID = AD.AD_AllocationsDutyID
									  LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
									  LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
									  LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
									WHERE MJ.IsActive=1
									  and MJ.TeamID = @TeamID

							INSERT INTO @TempHistory
									  (
											historytype,
											attributeid,
											userid,
											history,
											CreateDateTime
									  )
								SELECT @HistoryTypeJob AS historytype,
									   AllocateJobID,
									   @vuserID,
									   'New Job created by '+@vname+' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +'.',
									   GETDATE()
								FROM @TempJob


				 END

	  			IF  (@EditType = 'DELETE')
				 BEGIN

	  				INSERT INTO @TempHistory
							( historytype, attributeid, HistorySubType,
								CreateDateTime, userid, history
							)
					SELECT @HistoryTypePerson AS historytype,
							AllocationsSPID AS attributeid,
							'PH' as HistorySubType,
							getdate(),
							@vuserID,
							'Sickness deleted by '
							+ @vname +
							+ ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + '.'
					FROM  @TempAllocations AL
					WHERE DutyType = 8
					  AND LeaveType  IN (3,4,5)
					  AND LeaveStatus <> 9

				     UPDATE LA
					    SET LA.Deleted = 1
					   FROM @TempAllocations AL
					  INNER JOIN LeaveApplications LA ON AL.SchedulingPersonID = LA.SchedulingPersonID
													 AND AL.DutyDate = LA.dDate
					  WHERE la.LeaveTypeID IN (3,4,5)

				   UPDATE ASP
				      SET ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID,
						  ASP_DutyTeamID = AL_SchedulingTeamID,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = 0,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_ChargingStatus = 0,
						  ASP_LeaveDuration = 0,
						  ASP_LeaveType = 0,
						  ASP_LeaveStatus = 0,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
				    FROM @TempAllocations TL
				   INNER JOIN AllocationsScheduledPersons asp on TL.AllocationsSPID = ASP_AllocationsSPID
				   INNER JOIN AllocationsDuties AD on ASP_AllocationsID = AD_AllocationsID
												  AND ASP_iDay = AD_iDay
				   INNER JOIN Allocations AL on AL_AllocationsID = asp.ASP_AllocationsID
				   WHERE TL.DutyType = 8
				     AND TL.LeaveType  IN (3,4,5)
					 AND AD.AD_DutyType = 7

			     END

                  UPDATE AL
                     SET al.AD_DutyStatus = TAL.DutyStatus,
						 al.AD_UpdatedBy = @vuserID,
						 al.AD_UpdatedDate = GETUTCDATE()
                    FROM AllocationsDuties AL
				   INNER JOIN @TempAllocations TAL ON TAL.AllocationsDutyID = AL.AD_AllocationsDutyID
				    and tal.RecordAction = 'U'

				   UPDATE ASP
				      SET ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID,
						  ASP_DutyTeamID = @SchedulingTeamID,
						  ASP_MarkedOverTime = 0,
						  ASP_OverTimeHours = 0,
						  ASP_OverTwelveStatus = 0,
						  ASP_OverTwelveHrs = 0,
						  ASP_IsOverseasOverTwelve = 0,
						  ASP_ChargingStatus = 0,
						  ASP_UpdatedBy = @vuserID,
						  ASP_UpdatedDate = GETUTCDATE()
				    FROM @TempAllocations TL
				   INNER JOIN AllocationsScheduledPersons asp on TL.AllocationsSPID = ASP_AllocationsSPID
				   INNER JOIN AllocationsDuties AD on ASP_AllocationsID = AD_AllocationsID
												  AND ASP_iDay = AD_iDay
				   WHERE TL.DutyType NOT IN (7,8,9,11,12)
				     AND AD.AD_DutyType = 7
					 AND TL.RecordAction = 'U'
					 AND ISNULL(TL.ROTACreationStatus,0) = 0


                IF ( @EditType = 'APPLYROTA' AND @SetMarkAttention = 1 )
				 BEGIN

				   UPDATE AL
				      SET AL.AD_isAttention = 1,
					      al.AD_UpdatedBy = @vuserID,
						  al.AD_UpdatedDate = getutcdate()
					 FROM AllocationsDuties AL
					INNER JOIN @TempAllocations TL on TL.AllocationsDutyID = AD_AllocationsDutyID
					WHERE TL.RecordAction = 'S'

	  					  INSERT INTO @TempHistory
									( historytype, attributeid, HistorySubType,
									  CreateDateTime, userid, history
									)
							SELECT @HistoryTypeDuty AS historytype,
								   AllocationsDutyID AS attributeid,
								   'DH' as HistorySubType,
								   getdate(),
								   @vuserID,
								   'Marked for Attention by '
								   + @vname +
								   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + '.'
							FROM   @TempAllocations AL
							WHERE RecordAction ='S'

	  					  INSERT INTO @TempHistory
									( historytype, attributeid, HistorySubType,
									  CreateDateTime, userid, history
									)
							SELECT @HistoryTypeDuty AS historytype,
								   AllocationsSPID AS attributeid,
								   'PH' as HistorySubType,
								   getdate(),
								   @vuserID,
								   'Marked for Attention by '
								   + @vname +
								   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm') + '.'
							FROM   @TempAllocations AL
							WHERE RecordAction ='S'

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


		    END;

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
			SELECT AllocationsID,
				   AllocationsDutyID,
				   AllocationsSPID,
				   0,
				   @vuserID,
				   GETUTCDATE()
			  FROM @TempAllocations TA

			    EXEC @ReturnValue =  usp_Create_DutyAccPeriodSummarySP @StartDate,@EndDate, @SchedulingPersonID, @pNetLogin

				IF ( ISNULL(@ReturnValue,0) > 0 )
				 BEGIN
				    THROW 51032,'Error while creatiing duty acc period summary' , 1;
				 END

				EXEC @ReturnValue = usp_CreateWTDBreach @StartDate,@EndDate, @TeamID, @pNetLogin, @SchedulingPersonID

				IF ( ISNULL(@ReturnValue,0) > 0 )
				 BEGIN
				    THROW 51033,'Error while calculating WTD breach' , 1;
				 END

			   IF ( @@TRANCOUNT	> 0 )
				BEGIN
				 COMMIT  TRANSACTION
				END

			      SELECT 0 as SPExecStatus,
						'Success' as SPMessage

			   IF  (@EditType = 'APPLYROTA' )
 				BEGIN
				  SELECT DutyName,
				         StartTime,
						 EndTime,
						 DutyDate,
						 @schedulingTeamName as SchedulingTeamName,
						 SchedulingPersonID,
						 MarkedOvertime,
						 ChargingStatus,
						 LeaveStartTime,
						 LeaveEndTime,
						 CASE WHEN @schedulingTeamName <> st.schedulingTeamName
							  THEN st.schedulingTeamId
							  ELSE NULL END AS AdditionalTeamID,
						 CASE WHEN @schedulingTeamName <> st.schedulingTeamName
							  THEN st.schedulingTeamName
							  ELSE NULL END AS AdditionalTeamName
				    FROM @TempAllocations TA
					LEFT JOIN schedulingTeams ST on st.schedulingTeamId = ta.DutyTeamID
				   WHERE RecordAction = 'S'

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

       IF ( ERROR_NUMBER() in (1204,1205,1222,3930) )
		 SELECT ERROR_NUMBER() as SPExecStatus,
				'Somebody else is also editing this duty. Please try again' as SPMessage
	    ELSE
	 	 SELECT ISNULL(@@IDENTITY,ERROR_NUMBER()) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage

	END CATCH;

END