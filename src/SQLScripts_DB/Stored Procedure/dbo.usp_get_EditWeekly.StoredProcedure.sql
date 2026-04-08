USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_EditWeekly]    Script Date: 30/03/2026 13:42:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER             PROCEDURE  [dbo].[usp_get_EditWeekly]
@pStartDate                 DATE,
@pEndDate                   DATE,
@pTeamID			        INT,
@pNetLogin                  VARCHAR(30),
@pSchedulingPersonID        VARCHAR(100) = NULL,
@pShowOnlyUnAllocatedDuty   INT = NULL

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

	DECLARE @ArchiveDataFlag           BIT = 0,
	        @AllocDataFlag             BIT = 0,
	        @TempAllocExistsFlag       BIT = 0,
			@ShowOnlyUnAllocatedDuty   BIT = 0,
			@NoOfWeeks				   INT = 0,
			@vStartWeek				   INT = 0,
			@vEndWeek				   INT = 0,
			@IsWeekCreated			   BIT = 0,
			@SPFlag					   BIT = 0;

	DECLARE @TempLabelList VARCHAR(MAX);

	DECLARE @FreeLancerList TABLE (ScheduledPersonID INT)
	DECLARE @SPFilterList   TABLE (ScheduledPersonID INT)

	DECLARE @TempAllocations TABLE ( AL_AllocationsID INT,
								    AL_SchedulingTeamID INT,								    
									WeekNumber INT,
								    AD_AllocationsDutyID INT,
									AD_DutyName NVARCHAR(100),
									AD_Duration INT,
									AD_iDay INT,
									AD_StartTimeSec INT,
									AD_EndTimeSec INT,
									AD_DutyBreakTime INT,
									AD_DutyDate DATE,
									AD_DutyStartTimeUTC DATETIME,
									AD_DutyEndTimeUTC DATETIME,
									AD_DutyStartTimeLocal DATETIME,
									AD_DutyEndTimeLocal DATETIME,
									AD_MasterDutyID INT,
									AD_DutyType INT,
									AD_DutyStatus INT, 
									AD_DutyColourID INT,
									AD_Comments INT,
									AD_isAttention INT,
									AD_isRequest BIT,
									AD_DutyProgramID1 INT,
									AD_DutyProgramID2 INT,
									AD_DutyProgramID3 INT,
									AD_DutyProgramID4 INT,
									AD_DutyProgramID5 INT,
									AD_DutyProgramID6 INT,
									AD_PlannedDuration INT,
									AD_PlannedDutyBreakTime INT,
									ASP_OverTwelveStatus  INT,
									ASP_OverTwelveHrs INT,
									ASP_IsOverseasOverTwelve BIT,
									AD_IsNeedCovering BIT,
									AD_IsOverrideOver12 BIT,
									ASP_AllocationsSPID INT,
									ASP_AllocationsDutyID INT,
									ASP_SchedulingPersonID INT,
									ASP_iDay INT,
									ASP_SortCode NVARCHAR(30),
									ASP_LeaveStatus  INT,
									ASP_LeaveType INT,
									ASP_DutyDate DATE,
									ASP_WIADStatus INT,
									ASP_MarkedOverTime BIT,
									ASP_OverTimeHours  INT,
									ASP_DutyTeamID INT,
									ASP_SigninStartTime INT,
									ASP_SigninEndTime INT,
									ASP_SigninStatus INT,
									ASP_SigninINBuilding INT,
									ASP_EDPStatus INT,
									ASP_Comments INT,
									ASP_LeaveStartTimeSec INT,
									ASP_LeaveEndTimeSec INT,
									ASP_LeaveStartTimeLocal DATETIME,
									ASP_LeaveEndTimeLocal DATETIME,
									ASP_LeaveDuration INT,
									ASP_UnderElevenBreakStatus INT,
									ASP_CalculatedUnderElevenHrs INT,
									ASP_OverrideUnderElevenHrs  INT,
									ASP_RequestsStatus  INT,
									ASP_RequestsCount  INT,
									ASP_LockRequestsStatus INT,
									ASP_ChargingStatus INT,
									ASP_ChargingTeamID INT,
									ASP_LeaveColourID INT,
									AAP_AllocationsAPID  INT)

	 SELECT TOP 1 @IsWeekCreated = 1
	   FROM Allocations
	  INNER JOIN TimeDimension td on td.ixYearWeek = AL_WeekNumber
	  WHERE td.dDateTime between @pstartDate and @pEndDate
		AND AL_Status in (0,1)
		AND AL_SchedulingTeamID = @pTeamID

	IF ( @pSchedulingPersonID IS NULL )
	 SELECT CASE WHEN ISNULL(@IsWeekCreated,0) = 1 THEN 1 ELSE 0 END AS IsShowEditWeekly

	IF ( @pSchedulingPersonID IS NOT NULL )
	 BEGIN
	  SET @SPFlag = 1
	  INSERT INTO @SPFilterList
	  select value FROM string_split(@pSchedulingPersonID,',')
	 END
	ELSE
	 BEGIN
	  INSERT INTO @SPFilterList VALUES(1)
	 END

	IF ( @IsWeekCreated = 0 )
		BEGIN

		   SELECT @vStartWeek = MIN(ixYearWeek),
		          @vEndWeek = MAX(ixYearWeek)
		     FROM TimeDimension
			WHERE dDateTime between @pstartDate and @pEndDate

		  EXEC usp_ViewWeekly @pTeamID, @vStartWeek, @vEndWeek, @pNetLogin

		  RETURN 0

		END


	IF ( @SPFlag = 0 AND ISNULL(@pShowOnlyUnAllocatedDuty,0) = 1 )
	 BEGIN
	  SET @ShowOnlyUnAllocatedDuty = 1
	 END

	IF EXISTS ( select top 1 TD.ID
				  from ArchivedWeeks AW
				 inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
				 where td.dDateTime between @pstartDate and @pEndDate
			   )
		SET @ArchiveDataFlag = 1

	IF EXISTS (  select top 1 TD1.ID
				   from TimeDimension TD1
				  WHERE TD1.dDateTime between @pStartDate and @pEndDate
				    AND NOT EXISTS
								 (
								  select TD.ID
								   from ArchivedWeeks AW
								   inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
								   where TD1.ID = TD.ID
								 )
				)
		 SET @AllocDataFlag = 1

	SELECT @NoOfWeeks = COUNT(DISTINCT td.ixYearWeek)
	FROM dbo.TimeDimension AS td
	WHERE td.dDateTime >= @pStartDate
	  AND td.dDateTime <= @pEndDate

	INSERT INTO @FreeLancerList
	SELECT DISTINCT STL.ScheduledPersonID AS ScheduledPersonID
	  FROM ScheduledPersonTeam_LINK (nolock) AS STL
	 INNER JOIN schedulingTeams ST (nolock) ON ST.schedulingTeamId = STL.TeamID
	 WHERE ST.schedulingTeamName in ('Other BBC', 'Freelancers','Apprentices')
	   AND STL.IsHomeTeam = 1
	   AND STL.scheduledType = 1
	   AND @pStartDate <=   STL.enddate
	   AND @pEndDate  >=  STL.startdate

	IF @ShowOnlyUnAllocatedDuty = 1
	 BEGIN

	    SELECT AD_DutyName		AS DutyName,
			   AD_Duration		AS Duration,
			   AL_WeekNumber	AS WeekNumber,
			   AD_iDay			AS iDay,
			   AD_StartTimeSec	AS StartTime,
			   AD_EndTimeSec	AS EndTime,
			   AD_AllocationsDutyID AS ID,
			   ST.SchedulingTeamId,
			   0				AS SchedulingPersonID,
			   FORMAT(AD_DutyDate, 'yyyy-MM-dd') AS DutyDate,
			   AD_MasterDutyID	AS MasterDutyId,
			   AD_DutyStatus	AS isActive,
			   0				AS DutyTeamID,
			   'UL'				AS DisplayGrid ,
			   count(AD_DutyName) over ( partition by AD_DutyDate, AD_DutyName ) AS DutyInstances
          FROM Allocations AS AL (nolock)
		 INNER JOIN AllocationsDuties AD on AL.Al_AllocationsID = AD.AD_AllocationsID
		 INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL.AL_WeekNumber and TD.ixDayInWeek = AD.AD_iDay
		 INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  AL.AL_SchedulingTeamID
		 WHERE AD_DutyName is not null
		   AND AD_DutyStatus = 0
		   AND ST.SchedulingTeamId = @pteamId
		   AND TD.dDateTime between @pStartDate and @pEndDate
		   AND @AllocDataFlag = 1
		  UNION ALL
	    SELECT AD_DutyName		AS DutyName,
			   AD_Duration		AS Duration,
			   AL_WeekNumber	AS WeekNumber,
			   AD_iDay			AS iDay,
			   AD_StartTimeSec	AS StartTime,
			   AD_EndTimeSec	AS EndTime,
			   AD_AllocationsDutyID AS ID,
			   ST.SchedulingTeamId,
			   0				AS SchedulingPersonID,
			   FORMAT(AD_DutyDate, 'yyyy-MM-dd') AS DutyDate,
			   AD_MasterDutyID	AS MasterDutyId,
			   AD_DutyStatus	AS isActive,
			   0				AS DutyTeamID,
			   'UL'				AS DisplayGrid ,
			   count(AD_DutyName) over ( partition by AD_DutyDate, AD_DutyName ) AS DutyInstances
          FROM Allocations_ARCH AS AL (nolock)
		 INNER JOIN AllocationsDuties_ARCH AD on AL.Al_AllocationsID = AD.AD_AllocationsID
		 INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL.AL_WeekNumber and TD.ixDayInWeek = AD.AD_iDay
		 INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  AL.AL_SchedulingTeamID
		 WHERE AD_DutyName is not null
		   AND AD_DutyStatus = 0
		   AND ST.SchedulingTeamId = @pteamId
		   AND TD.dDateTime between @pStartDate and @pEndDate
		   AND @ArchiveDataFlag = 1
     END
    ELSE
     BEGIN

					SELECT @TempLabelList =   '#'+STUFF(
					(SELECT '#' + cast(id  as varchar)+'#'
					     from Programmes
					    where Programme = 'Acting'
					      FOR XML PATH(''), TYPE
						).value('.', 'VARCHAR(MAX)'),1,1,'');

						INSERT INTO @TempAllocations
						SELECT *  FROM 
						(
							select  AL_AllocationsID,
								    AL.AL_SchedulingTeamID,								    
									TD.ixYearWeek as WeekNumber,
								    AD_AllocationsDutyID,
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
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveColourID
										 ELSE AD_DutyColourID END AS AD_DutyColourID,
									CASE WHEN AD_AllocationsID <> ASP_AllocationsID
									     THEN 0
										 WHEN ( AD_Comments IS NULL or AD_Comments = '')
										  THEN 0
										  ELSE 1 END AS AD_Comments,
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
									ASP_OverTwelveStatus,
									ASP_OverTwelveHrs,
									ASP_IsOverseasOverTwelve,
									AD_IsNeedCovering,
									AD_IsOverrideOver12,
									ASP_AllocationsSPID,
									ASP_AllocationsDutyID,
									ASP_SchedulingPersonID,
									ASP_iDay,
									ASP_SortCode,
									ASP_LeaveStatus,
									ASP_LeaveType,
									ASP_DutyDate,
									ASP_WIADStatus,
									ASP_MarkedOverTime,
									ASP_OverTimeHours,
									ASP_DutyTeamID,
									ASP_SigninStartTime,
									ASP_SigninEndTime,
									ASP_SigninStatus,
									ASP_SigninINBuilding,
									ASP_EDPStatus,
									CASE WHEN ASP_Comments IS NULL THEN 0 ELSE 1 END AS ASP_Comments,
									ASP_LeaveStartTimeSec,
									ASP_LeaveEndTimeSec,
									ASP_LeaveStartTimeLocal,
									ASP_LeaveEndTimeLocal,
									ASP_LeaveDuration,
									ASP_UnderElevenBreakStatus,
									ASP_CalculatedUnderElevenHrs,
									ASP_OverrideUnderElevenHrs,
									ASP_RequestsStatus,
									ASP_RequestsCount,
									ASP_LockRequestsStatus,
									ASP_ChargingStatus,
									ASP_ChargingTeamID,
									ASP_LeaveColourID,
									0 AS AAP_AllocationsAPID
							  FROM Allocations AS AL (nolock)
							 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
							 INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
																	   AND TD.ixDayInWeek = ASP_iDay
							 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
							 INNER JOIN @SPFilterList SFL on SFL.scheduledpersonid = CASE WHEN @SPFlag = 1
																						  THEN ASP_SchedulingPersonID
																						  ELSE SFL.scheduledpersonid END
							 WHERE dDateTime >= @pStartDate 
							   AND dDateTime <= @pEndDate
							   and AL_SchedulingTeamID = @pteamId
							   AND @AllocDataFlag = 1
							 UNION All
							 select AL.AL_AllocationsID,
								    AL.AL_SchedulingTeamID,
									TD.ixYearWeek as WeekNumber,
								    AD_AllocationsDutyID,
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
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveColourID
										 ELSE AD_DutyColourID END AS AD_DutyColourID,
									CASE WHEN AD_AllocationsID <> AAP_AllocationsID
									     THEN 0
										 WHEN ( AD_Comments IS NULL or AD_Comments = '')
										  THEN 0
										  ELSE 1 END AS AD_Comments,
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
									ASP_OverTwelveStatus,
									ASP_OverTwelveHrs,
									ASP_IsOverseasOverTwelve,
									AD_IsNeedCovering,
									AD_IsOverrideOver12,
									ASP_AllocationsSPID,
									ASP_AllocationsDutyID,
									ASP_SchedulingPersonID,
									ASP_iDay,
									AA.AAP_SortCode AS ASP_SortCode,
									ASP_LeaveStatus,
									ASP_LeaveType,
									ASP_DutyDate,
									ASP_WIADStatus,
									ASP_MarkedOverTime,
									ASP_OverTimeHours,
									ASP_DutyTeamID,
									ASP_SigninStartTime,
									ASP_SigninEndTime,
									ASP_SigninStatus,
									ASP_SigninINBuilding,
									ASP_EDPStatus,
									CASE WHEN AA.AAP_Comments IS NULL THEN 0 ELSE 1 END AS ASP_Comments,
									ASP_LeaveStartTimeSec,
									ASP_LeaveEndTimeSec,
									ASP_LeaveStartTimeLocal,
									ASP_LeaveEndTimeLocal,
									ASP_LeaveDuration,
									ASP_UnderElevenBreakStatus,
									ASP_CalculatedUnderElevenHrs,
									ASP_OverrideUnderElevenHrs,
									ASP_RequestsStatus,
									ASP_RequestsCount,
									ASP_LockRequestsStatus,
									ASP_ChargingStatus,
									ASP_ChargingTeamID,
									ASP_LeaveColourID,
									AA.AAP_AllocationsAPID
							  FROM Allocations AS AL (nolock)
							 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
							 INNER JOIN AllocationsAddPersons AA on AL_AllocationsID = AAP_AllocationsID
																AND TD.ixDayInWeek = AA.AAP_iDay
							 INNER JOIN AllocationsScheduledPersons ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
							 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
							 INNER JOIN Allocations ALA ON ALA.AL_AllocationsID = AD_AllocationsID
							 INNER JOIN @SPFilterList SFL on SFL.scheduledpersonid = CASE WHEN @SPFlag = 1
																						  THEN ASP_SchedulingPersonID
																						  ELSE SFL.scheduledpersonid END
							 WHERE dDateTime >= @pStartDate 
							   AND dDateTime <= @pEndDate
							   and AL.AL_SchedulingTeamID = @pteamId
							   AND ALA.AL_Status <> 9
							   AND @AllocDataFlag = 1
						 ) Allocations;

						INSERT INTO @TempAllocations
						SELECT * FROM
						(
							select  AL.AL_AllocationsID,
								    AL.AL_SchedulingTeamID,								    
									TD.ixYearWeek as WeekNumber,
								    AD_AllocationsDutyID,
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
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveColourID
										 ELSE AD_DutyColourID END AS AD_DutyColourID,
									CASE WHEN (AD_Comments IS NULL) or (AD_Comments = '') THEN 0 ELSE 1 END AS AD_Comments,
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
									ASP_OverTwelveStatus,
									ASP_OverTwelveHrs,
									ASP_IsOverseasOverTwelve,
									AD_IsNeedCovering,
									AD_IsOverrideOver12,
									ASP_AllocationsSPID,
									ASP_AllocationsDutyID,
									ASP_SchedulingPersonID,
									ASP_iDay,
									ASP_SortCode,
									ASP_LeaveStatus,
									ASP_LeaveType,
									ASP_DutyDate,
									ASP_WIADStatus,
									ASP_MarkedOverTime,
									ASP_OverTimeHours,
									ASP_DutyTeamID,
									ASP_SigninStartTime,
									ASP_SigninEndTime,
									ASP_SigninStatus,
									ASP_SigninINBuilding,
									ASP_EDPStatus,
									CASE WHEN ASP_Comments IS NULL THEN 0 ELSE 1 END AS ASP_Comments,
									ASP_LeaveStartTimeSec,
									ASP_LeaveEndTimeSec,
									ASP_LeaveStartTimeLocal,
									ASP_LeaveEndTimeLocal,
									ASP_LeaveDuration,
									ASP_UnderElevenBreakStatus,
									ASP_CalculatedUnderElevenHrs,
									ASP_OverrideUnderElevenHrs,
									ASP_RequestsStatus,
									ASP_RequestsCount,
									ASP_LockRequestsStatus,
									ASP_ChargingStatus,
									ASP_ChargingTeamID,
									ASP_LeaveColourID,
									0  AS AAP_AllocationsAPID
							  FROM Allocations_ARCH AS AL (nolock)
							 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
							 INNER JOIN AllocationsScheduledPersons_ARCH ASP on AL_AllocationsID = ASP_AllocationsID
																	   AND TD.ixDayInWeek = ASP_iDay
							 INNER JOIN AllocationsDuties_ARCH AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
							 INNER JOIN @SPFilterList SFL on SFL.scheduledpersonid = CASE WHEN @SPFlag = 1
																						  THEN ASP_SchedulingPersonID
																						  ELSE SFL.scheduledpersonid END
							 WHERE dDateTime >= @pStartDate 
							   AND dDateTime <= @pEndDate
							   and AL.AL_SchedulingTeamID = @pteamId
							   AND @ArchiveDataFlag = 1
							 UNION All
							 select AL.AL_AllocationsID,
								    AL.AL_SchedulingTeamID,								    
									TD.ixYearWeek as WeekNumber,
								    AD_AllocationsDutyID,
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
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveColourID
										 ELSE AD_DutyColourID END AS AD_DutyColourID,
									CASE WHEN AD_AllocationsID <> AAP_AllocationsID
									     THEN 0
										 WHEN ( AD_Comments IS NULL or AD_Comments = '')
										  THEN 0
										  ELSE 1 END AS AD_Comments,
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
									ASP_OverTwelveStatus,
									ASP_OverTwelveHrs,
									ASP_IsOverseasOverTwelve,
									AD_IsNeedCovering,
									AD_IsOverrideOver12,
									ASP_AllocationsSPID,
									ASP_AllocationsDutyID,
									ASP_SchedulingPersonID,
									ASP_iDay,
									AA.AAP_SortCode AS ASP_SortCode,
									ASP_LeaveStatus,
									ASP_LeaveType,
									ASP_DutyDate,
									ASP_WIADStatus,
									ASP_MarkedOverTime,
									ASP_OverTimeHours,
									ASP_DutyTeamID,
									ASP_SigninStartTime,
									ASP_SigninEndTime,
									ASP_SigninStatus,
									ASP_SigninINBuilding,
									ASP_EDPStatus,
									CASE WHEN AAP_Comments IS NULL THEN 0 ELSE 1 END AS ASP_Comments,
									ASP_LeaveStartTimeSec,
									ASP_LeaveEndTimeSec,
									ASP_LeaveStartTimeLocal,
									ASP_LeaveEndTimeLocal,
									ASP_LeaveDuration,
									ASP_UnderElevenBreakStatus,
									ASP_CalculatedUnderElevenHrs,
									ASP_OverrideUnderElevenHrs,
									ASP_RequestsStatus,
									ASP_RequestsCount,
									ASP_LockRequestsStatus,
									ASP_ChargingStatus,
									ASP_ChargingTeamID,
									ASP_LeaveColourID,
									AA.AAP_AllocationsAPID
							  FROM Allocations_ARCH AS AL (nolock)
							 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
							 INNER JOIN AllocationsAddPersons_ARCH AA on AL_AllocationsID = AAP_AllocationsID
																AND TD.ixDayInWeek = AA.AAP_iDay
							 INNER JOIN AllocationsScheduledPersons_ARCH ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
							 INNER JOIN AllocationsDuties_ARCH AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
							 INNER JOIN Allocations_ARCH ALA ON ALA.AL_AllocationsID = ASP_AllocationsID
							 INNER JOIN @SPFilterList SFL on SFL.scheduledpersonid = CASE WHEN @SPFlag = 1
																						  THEN ASP_SchedulingPersonID
																						  ELSE SFL.scheduledpersonid END
							 WHERE dDateTime >= @pStartDate 
							   AND dDateTime <= @pEndDate
							   AND AL.AL_SchedulingTeamID = @pteamId
							   AND ALA.AL_Status <> 9
							   AND @ArchiveDataFlag = 1
						 ) AllocArch;

	    SELECT al.staffnumber                     AS StaffNumber,
			   CASE WHEN al.dutyname IS NULL THEN 'U'
					WHEN AL.AD_DutyType IN (8,11,12)
					     THEN CASE WHEN ASP_LeaveType = 1
								   THEN 'Leave'
								   WHEN ASP_LeaveType = 2
								   THEN 'OFF Leave'
								   WHEN ASP_LeaveType = 3
								   THEN 'Sick'
								   WHEN ASP_LeaveType = 4
								   THEN 'U-Sick'
								   WHEN ASP_LeaveType = 5
								   THEN '-Sick'
								   WHEN ASP_LeaveType = 7
								   THEN 'Absent'
							   END
			        ELSE al.dutyname
					 END		  			      AS DutyName,
			   al.duration                        AS Duration,
			   al.weeknumber                      AS WeekNumber,
			   al.iday                            AS iDay,
			   al.starttime                       AS StartTime,
			   al.endtime                         AS EndTime,
			   al.sortcode                        AS SortCode,
			   al.dutycomments                    AS DutyComments,
			   al.personcomments                  AS PersonComments,
			   al.markedovertime                  AS MarkedOvertime,
			   al.markedsickness                  AS MarkedSickness,
			   al.unallocated                     AS UnAllocated,
			   al.id                              AS ID,
			   al.schedulingteamid                AS SchedulingTeamId,
			   al.schedulingpersonid              AS SchedulingPersonID,
			   FORMAT(al.dutydate, 'yyyy-MM-dd')  AS DutyDate,
			   al.ispublished                     AS isPublished,
			   al.ishometeam                      AS IsHomeTeam,
			   al.markwiad                        AS MarkWiad,
			   al.markactual                      AS MarkActual,
			   al.isattention                     AS isAttentionClsName,
			   al.isrequest				          AS isRequest,
			   al.dutybreaktime                   AS dutyBreakTime,
			   al.dutycolorid                     AS dutyColorId,
			   al.MasterDutyId                    AS MasterDutyId,
			   al.isactive                        AS isActive,
			   al.iseditable                      AS isEditable,
			   al.paymenttypename                 AS pay,
			   al.displayname                     AS DisplayName,
			   al.DisplayLastName                 AS DisplayLastName,
			   al.DisplayFirstName                AS DisplayFirstName,
			   al.schedulingTeamName              AS schedulingTeamName,
			   al.IsSigninAllowed                 AS IsSigninAllowed,
			   al.Signindays                      AS Signindays,
			   al.eft                             AS EFT,
			   al.acc                             AS ACC,
			   al.contractedhours                 AS ContractedHours,
			   CASE WHEN al.accdays IS NULL THEN  AL.CurrWeekNoOfDays
			        ELSE al.accdays END           AS AccDays,
			   al.OverTimeHrs                     AS OverTimeHrs,
			   al.manualedp                       AS ManualEDP,
			   al.signin                          AS signin,
			   al.inbuilding                      AS inbuilding,
               al.ActionNameForSignin             AS ActionNameForSignin,
			   al.ImageNameSignin                 AS ImageNameSignin,
			   al.colourbackground                AS ColourBackground,
			   al.colourfont                      AS ColourFont,
			   al.personbackgroundcolour          AS PersonBackgroundColour,
			   al.personfontcolour                AS PersonFontColour,
			   CASE WHEN AL.WeekDuration is null then
			             CASE WHEN AL.CurrWeekDuration = 0 THEN '00.00'
						      ELSE CAST(AL.CurrWeekDuration AS VARCHAR)
                         END
					ELSE CAST(AL.WeekDuration AS VARCHAR) END   AS WeekDuration,
			   AL.AccPeriod                       AS AccPeriod,
			   al.CostCode                        AS CostCode,
			   al.staffid                         AS StaffID,
			   al.TriangleColour                  AS TriangleColour,
			   AL.CountLeave                      AS CountLeave,
			   AL.LeaveApproved                   AS LeaveApproved,
			   AL.LeaveDeleted                    AS LeaveDeleted,
			   AL.LeaveShortNotice                AS LeaveShortNotice,
			   AL.Leaveoversummer                 AS Leaveoversummer,
			   AL.LeaveisOK                       AS LeaveisOK,
			   AL.EditDuty                        AS EditDuty,
			   AL.ShowLock,
			   AL.LockIconColour,
			   AL.ReqCount,
			   AL.ShowEDPIcon                     AS ShowEDPIcon,
			   AL.ShowWIAD                        AS ShowWIAD,
			   CASE WHEN WTD.isapproved = 0 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 1
				    WHEN WTD.isapproved = 1 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 2
					WHEN WTD.isapproved = 0 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 1
				    WHEN WTD.isapproved = 1 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 2
				 ELSE 0
			    END                               AS WTDBreachClassName,
			   AL.contextMenuClsName              AS contextMenuClsName,
			   AL.DisplayGrid                     AS DisplayGrid,
			   AL.MarkOverTwelve                  AS MarkOverTwelve,
			   AL.IsUnderElevenBreak              AS IsUnderElevenBreak,
			   AL.IsUnderElevenBreakOverride      AS IsUnderElevenBreakOverride,
			   AL.DutyTeamID 			          AS DutyTeamID,
			   AL.IsDutyFromOtherTeam             AS IsDutyFromOtherTeam,
			   AL.dutyProgramId                   AS dutyProgramId,
			   count(AL.DutyName) over ( partition by AL.dutydate, AL.dutyname,AL.schedulingpersonid) AS DutyInstances,
			   AL.LeaveStartTime                  AS LeaveStartTime,
			   AL.LeaveEndTime                    AS LeaveEndTime,
			   AL.LeavePDL                        AS LeavePDL,
			   AL.FWANotesFlag                    AS FWANotesFlag,
			   AL.NetLogin                        AS NetLogin,
			   AL.LeaveID                         AS LeaveID,
			   AL.dutyProgramId2                  AS dutyProgramId2,
			   AL.dutyProgramId3                  AS dutyProgramId3,
			   AL.dutyProgramId4                  AS dutyProgramId4,
			   AL.dutyProgramId5                  AS dutyProgramId5,
			   AL.dutyProgramId6                  AS dutyProgramId6,
			   AL.isAgreed                        AS IsAgreed,
			   AL.IsNeedCovering                  AS IsNeedCovering,
			   AL.ActingFlag                      AS ActingFlag,
			   AL.TotalPlannedDuration            AS TotalPlannedDuration,
			   AL.IsOverrideOver12                AS IsOverrideOver12,
			   AL.AD_AllocationsDutyID			  AS AllocationsDutyID,
			   AL.ASP_AllocationsSPID			  AS AllocationsSPID
	   FROM
		   ( SELECT AD.AD_DutyName			 AS DutyName,
					CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
					AL_WeekNumber			 AS WeekNumber,
					td.ixDayInWeek			 AS iDay,
					AD.AD_StartTimeSec		 AS StartTime,
					AD.AD_EndTimeSec		 AS EndTime,
					ISNULL(AD.AD_Comments,0)  AS DutyComments,
					ISNULL(AD.ASP_Comments,0)  AS PersonComments,
					ISNULL(ASP_MarkedOverTime,0)		  AS MarkedOvertime,
					CASE WHEN AD.AD_DutyType IN (8,11) AND ASP_LeaveType IN ( 3,4,5)
									   THEN 1
									   ELSE 0
								   END AS MarkedSickness,
					0						  AS UnAllocated,
					AL.AL_AllocationsID		  AS  ID,
					AL.AL_SchedulingTeamId		AS SchedulingTeamId,
					spl.ScheduledPersonID	AS SchedulingPersonID,
					td.dDateTime			AS DutyDate,
					CASE WHEN ISNULL(AD.ASP_OverTimeHours,0) >= 0 AND ISNULL(AD.AD_isAttention,0) = 1 THEN 1
						  WHEN ISNULL(AD.ASP_OverTimeHours,0) < 0 THEN 0
						  ELSE ISNULL(AD.AD_isAttention,0)
						  END AS isAttention,
					ISNULL(AD.AD_isRequest,0)				AS isRequest,
					AD.AD_DutyBreakTime			AS dutyBreakTime,
					AD.AD_DutyColourID AS dutyColorId,
					AL.AL_Status				AS isPublished,
					SPL.IsHomeTeam				AS IsHomeTeam,
					CASE WHEN AD.ASP_WIADStatus	= 1 THEN 1 ELSE 0 END	AS MarkWiad,
					CASE WHEN AD.ASP_WIADStatus	= 2 THEN 1 ELSE 0 END	as MarkActual,
					AD_DutyStatus				as IsActive,
					1							as isEditable,
					ISNULL(ad.AD_MasterDutyID,0)			AS MasterDutyId,
					CASE WHEN ASP_OverTwelveStatus = 0 THEN 9
						 WHEN ASP_OverTwelveStatus = 1 THEN -1
						 WHEN ASP_OverTwelveStatus = 2 THEN 1
						 WHEN ASP_OverTwelveStatus = 9 THEN 0
						 ELSE 9 END						 AS MarkOverTwelve,
					CASE WHEN ASP_UnderElevenBreakStatus = 0 THEN 0
						 WHEN ASP_UnderElevenBreakStatus IN (1,2) THEN 1
						 ELSE 0 END 			AS IsUnderElevenBreak,
					CASE WHEN ASP_UnderElevenBreakStatus = 0 THEN 0
						 WHEN ASP_UnderElevenBreakStatus = 2 THEN 1
						 ELSE 0 END 			AS IsUnderElevenBreakOverride,
					ASP_DutyTeamID				AS DutyTeamID,
					AD_DutyProgramID1			AS dutyProgramId,
					AD_DutyProgramID2			AS dutyProgramId2,
					AD_DutyProgramID3			AS dutyProgramId3,
					AD_DutyProgramID4			AS dutyProgramId4,
					AD_DutyProgramID5			AS dutyProgramId5,
					AD_DutyProgramID6			AS dutyProgramId6,
					case when CHARINDEX('#'+cast(AD_DutyProgramID1 as varchar)+'#', @TempLabelList, 1) > 0 then 1
					     when CHARINDEX('#'+cast(AD_DutyProgramID2 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID3 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID4 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID5 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID6 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 else 0 end as ActingFlag,
					CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.al_SchedulingTeamId
					 THEN 1 ELSE 0 END	IsDutyFromOtherTeam,
				    ISNULL(rft.PaymentTypeShortCode,'')  AS paymenttypename,
				   sp.UD_Displayname  AS DisplayName,
				   COALESCE(Asp_SortCode, aap_sortcode, spl.sortcode) AS sortcode,
				   scp.uc_eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   scp.uc_PartTimeEDP                             AS ContractedHours,
				   APD_AccPeriodDays						   AS accdays,
				   uc_manualedp                                AS manualedp,
				   ud_DisplayLastName                          AS DisplayLastName,
				   ud_DisplayFirstName                         AS DisplayFirstName,
				   st.schedulingTeamName                       AS schedulingTeamName,
				   ISNULL(st.signin,0)                         AS IsSigninAllowed,
				   ISNULL(st.signindays,0)                     AS Signindays,
				   case when ASP_SigninStatus is null then 0
				        when AD_StartTimeSec = ASP_SigninStartTime
					     AND AD_EndTimeSec = ASP_SigninEndTime then 2
					     ELSE  ISNULL(ASP_SigninStatus,0)  end       		   AS signin,
				   ISNULL(ASP_SigninINBuilding,0)							AS inbuilding,
				   case when ASP_SigninStatus = 1 and ( ASP_SigninStartTime <> AD_StartTimeSec
						OR ASP_SigninEndTime <> AD_EndTimeSec ) then 4
						when ASP_SigninStatus = 1 AND ASP_SigninINBuilding = 1 then 3
				        when ASP_SigninStatus = 1 AND ASP_SigninINBuilding <> 1 then 2
						when ASP_SigninStatus = 2 THEN 1
						else  1 end              AS ImageNameSignin,
				   case when ASP_SigninStatus = 1 and ( ASP_SigninStartTime <> AD_StartTimeSec
						OR ASP_SigninEndTime <> AD_EndTimeSec ) then 1
						when ASP_SigninStatus = 1 AND ASP_SigninINBuilding = 1 then 0
				        when ASP_SigninStatus = 1 AND ASP_SigninINBuilding <> 1 then 0
						else  1 end                            AS ActionNameForSignin,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   WHEN AD_DutyType IN (8,11,12)  THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#ebebeb'
				        ELSE spl.backgroundcolour END          AS PersonBackgroundColour,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#000000'
				        ELSE spl.fontcolour END                AS PersonFontColour,
				   ud_staffnumber                              AS staffnumber,
				   scp.uc_costcode                                AS CostCode,
				   ud_userid                                  AS staffid,
				   case when ASP_ChargingStatus = 5 AND ASP_ChargingTeamID = @pTeamID then 'None'
						when ASP_ChargingStatus = 4 AND ASP_ChargingTeamID = @pTeamID then 'Yellow'
						when ASP_ChargingStatus = 3 AND ASP_ChargingTeamID = @pTeamID then 'Green'
						when ASP_ChargingStatus = 2 AND ASP_ChargingTeamID = @pTeamID then 'Red'
						when ASP_ChargingStatus = 1 AND ASP_ChargingTeamID = @pTeamID then 'Blue'
						else NULL end AS TriangleColour,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.CountLeave  ELSE NULL END          AS CountLeave,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.Approved	ELSE NULL END			AS LeaveApproved,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.Deleted	ELSE NULL END			  AS LeaveDeleted,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.ShortNotice  ELSE NULL END         AS LeaveShortNotice,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.oversummer   ELSE NULL END         AS Leaveoversummer,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.isOK  ELSE NULL END                  AS LeaveisOK,
				   ASP_LockRequestsStatus						AS ShowLock,
				   CASE WHEN ASP_RequestsStatus	= 1 THEN 'B'
						WHEN ASP_RequestsStatus	= 2 THEN 'G'
						WHEN ASP_RequestsStatus	= 3 THEN 'O'
						WHEN ASP_RequestsStatus	= 4 THEN 'Y'
					ELSE '' END AS LockIconColour,
				   ASP_RequestsCount							AS ReqCount,
				   ISNULL(ASP_EDPStatus,0)                      AS ShowEDPIcon,
				   APD_AccPeriodDuration                        AS WeekDuration,
				   CASE WHEN scp.uc_manualedp = 1
				        THEN APD_TotalOverTimeHRS
						WHEN scp.uc_manualedp = 0
						THEN APD_AccPeriodDuration - (ISNULL(scp.uc_PartTimeEDP,0) * 3600 )
						ELSE NULL END                           AS OverTimeHrs,
				   AccPeriodDuration                       AS TotalPlannedDuration,
				  CAST((DATEDIFF(day,AccPeriodStartDate,td.dDateTime)/7)+1 AS VARCHAR)+'/'+
				 CAST((DATEDIFF(day,acd.APD_AccPeriodStartDate,acd.APD_AccPeriodEndDate)+1)/7 AS VARCHAR) AS AccPeriod,
				   CASE WHEN AD_DutyType IN (8,12) AND ASP_LeaveType <> 7 THEN 0
				        WHEN ISNULL(ASP_DutyTeamID,0) > 0
					     AND ASP_DutyTeamID <> AL.AL_schedulingTeamId 
						 AND AD_DutyType NOT IN ( 7,9 )
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0  THEN 0
					    WHEN ISNULL(SPL.ishometeam,1) IN (0,2) and ISNULL(ASP_WIADStatus,0) = 0
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0 THEN 0
						WHEN ISNULL(SPL.ishometeam,1) = 1
						 AND ( ISNULL(ASP_WIADStatus,0) IN (1,2)) THEN 0
						WHEN NOT(td.dDateTime between spl.StartDate and spl.EndDate) THEN 0
						ELSE 1 END          AS EditDuty,
				   CASE WHEN spl.IsHomeTeam IN (0,2) THEN 0
				        WHEN spl.IsHometeam = 1 AND AD_DutyName <> 'U'
				         AND ISNULL(ASP_WIADStatus,0) = 0 THEN 0
						WHEN NOT(td.dDateTime between spl.StartDate and spl.EndDate) THEN 0
						 ELSE 1 END AS ShowWIAD,
				   CASE WHEN AD_DutyType NOT IN (8,11,12) AND ( SPL.IsHomeTeam = 1 OR ASP_WIADStatus IN (1,2)) THEN 2
						WHEN AD_DutyType IN (8,11,12) AND ASP_LeaveType NOT IN (1,2)
							 AND  ( SPL.IsHomeTeam = 1 OR ASP_WIADStatus IN (1,2))  THEN 2
						 WHEN AD_DutyType IN (8,11,12) AND ASP_LeaveType IN (1,2)
							 AND  ( SPL.IsHomeTeam = 1 OR ASP_WIADStatus IN (1,2))  THEN 1
						 WHEN SPL.IsHomeTeam = 1 AND ISNULL(AD_AllocationsDutyID,0) = 0 THEN 2
						ELSE 0 END AS contextMenuClsName,
				   'AL' AS DisplayGrid,
				   CASE WHEN ( @NoOfWeeks = 1 AND APD_AccPeriodDutySummaryID IS NULL) THEN
				        SUM( CASE WHEN ISNULL(ASP_WIADStatus,0)=1 THEN 0
								  ELSE ISNULL(AD_Duration,0)-ISNULL(AD_DutyBreakTime,0) END) over (PARTITION BY AL_WeekNumber, spl.ScheduledPersonID)
				   ELSE 0 end as CurrWeekDuration,
				   CASE WHEN ( @NoOfWeeks = 1 AND APD_AccPeriodDutySummaryID IS NULL) THEN
				        SUM( case when isnull(ASP_WIADStatus,0)=1 then 0
					              when isnull(AD_Duration,0) > 0 THEN 1 END ) over (PARTITION BY AL_WeekNumber, spl.ScheduledPersonID)
				   ELSE 0 end as CurrWeekNoOfDays,
				   0 AS IsEpiredUSer,
			       ISNULL(LA.LeaveStartTime,0)                  AS LeaveStartTime,
			       ISNULL(LA.LeaveEndTime,0)                    AS LeaveEndTime,
				   CASE WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 0
				        THEN 0
						WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 1
						THEN 1
						ELSE  2 END  AS LeavePDL,
				   CASE WHEN SP.UD_FWANotes IS NOT NULL THEN 1 ELSE 0 END As FWANotesFlag,
				   UD_NetLogin					as NetLogin,
				   CASE WHEN ISNULL(LA.LeaveTypeID,0) NOT IN (3,4,5) THEN  LA.ID ELSE 0 END LeaveID,
				   LA.IsAgreed,
				   AD_IsNeedCovering			as IsNeedCovering,
				   AD_IsOverrideOver12			as IsOverrideOver12,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   AD_DutyType,
				   ASP_LeaveType,
				   CASE WHEN spl.IsHomeTeam IN (0,2) AND spl.IsAvailable = 0 AND AAP.AAP_AllocationsAPID IS NULL
						THEN 0 ELSE 1 END AS AddSPExclFilter
			  FROM Allocations AS AL (nolock)
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			 INNER JOIN @SPFilterList SFL on SFL.scheduledpersonid = CASE WHEN @SPFlag = 1
																		  THEN spl.ScheduledPersonID
																		  ELSE SFL.scheduledpersonid END
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL_WeekNumber
			 INNER JOIN UserDetails AS sp (nolock) ON UD_UserID = spl.ScheduledPersonID
			 LEFT JOIN 	@TempAllocations AD on AL.AL_SchedulingTeamID = AD.AL_SchedulingTeamID
							AND spl.ScheduledPersonID = AD.ASP_SchedulingPersonID
						     AND TD.ixYearWeek = AD.WeekNumber
							 AND td.ixDayInWeek = AD.ASP_iDay 
							 /* AL.AL_SchedulingTeamID = AD.AL_SchedulingTeamID
							 and spl.ScheduledPersonID = AD.ASP_SchedulingPersonID
							 AND TD.dDateTime = ASP_DutyDate */
		    LEFT JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0
			                                                              THEN ASP_DutyTeamID
																		  ELSE spl.TeamID END
			LEFT JOIN @FreeLancerList FRL ON FRL.scheduledpersonid =  spl.ScheduledPersonID
			LEFT JOIN UserConfigs scp (nolock) ON scp.UC_UserID = spl.ScheduledPersonID
							 AND TD.dDateTime BETWEEN scp.UC_StartDate AND scp.UC_EndDate
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = scp.UC_AccGroupID
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = scp.UC_PaymentTypeID
			LEFT JOIN REF_MasterDutyColours mdc (nolock) ON mdc.masterdutycolourid = AD_DutyColourID
			LEFT JOIN LeaveApplications LA  (nolock) ON TD.dDateTime = LA.dDate  AND LA.schedulingpersonid = spl.ScheduledPersonID
			                                        AND LA.Deleted = 0
													AND LA.LeaveTypesID > 0
			LEFT JOIN AllocationsAddPersons AAP on AL.AL_AllocationsID = AAP.AAP_AllocationsID
											  AND spl.ScheduledPersonID = AAP.AAP_SchedulingPersonID
											  AND td.ixDayInWeek = AAP.AAP_iDay
			LEFT JOIN AccPeriodROTADurationSummary ACR on ACR.ScheduledPersonID = spl.ScheduledPersonID
													AND TD.dDateTime between ACR.AccPeriodStartDate and ACR.AccPeriodEndDate
			LEFT JOIN AccPeriodDutySummary ACD on ACD.APD_ScheduledPersonID = spl.ScheduledPersonID
													AND TD.dDateTime between APD_AccPeriodStartDate and APD_AccPeriodEndDate
		     WHERE td.dDateTime between spl.startdate and spl.EndDate
			   AND SPL.scheduledType = 1
			   AND TD.dDateTime between @pStartDate and @pEndDate
			   AND AL.AL_SchedulingTeamID = @pteamId
			   AND NOT EXISTS ( SELECT 1
								  FROM AllocationsDelPersons ADP
								 WHERE ADP_AllocationsID = AL.AL_AllocationsID
								   AND spl.ScheduledPersonID = ADP.ADP_SchedulingPersonID)
			   AND @AllocDataFlag = 1
			 UNION ALL
			 SELECT AD.AD_DutyName			 AS DutyName,
					CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
					AL.AL_WeekNumber			 AS WeekNumber,
					td.ixDayInWeek			 AS iDay,
					AD.AD_StartTimeSec		 AS StartTime,
					AD.AD_EndTimeSec		 AS EndTime,
					ISNULL(AD.AD_Comments,0) AS DutyComments,
					ISNULL(AD.ASP_Comments,0) AS PersonComments,
					ISNULL(ASP_MarkedOverTime,0)		  AS MarkedOvertime,
					CASE WHEN AD.AD_DutyType IN (8,11) AND ASP_LeaveType IN ( 3,4,5)
									   THEN 1
									   ELSE 0
								   END AS MarkedSickness,
					0						  AS UnAllocated,
					AL.AL_AllocationsID		  AS  ID,
					AL.AL_SchedulingTeamId		AS SchedulingTeamId,
					spl.ScheduledPersonID	AS SchedulingPersonID,
					td.dDateTime			AS DutyDate,
					CASE WHEN ISNULL(AD.ASP_OverTimeHours,0) >= 0 AND ISNULL(AD.AD_isAttention,0) = 1 THEN 1
						  WHEN ISNULL(AD.ASP_OverTimeHours,0) < 0 THEN 0
						  ELSE ISNULL(AD.AD_isAttention,0)
						  END AS isAttention,
					ISNULL(AD.AD_isRequest,0)				AS isRequest,
					AD.AD_DutyBreakTime			AS dutyBreakTime,
					AD.AD_DutyColourID  AS dutyColorId,
					AL.AL_Status				AS isPublished,
					SPL.IsHomeTeam				AS IsHomeTeam,
					CASE WHEN AD.ASP_WIADStatus	= 1 THEN 1 ELSE 0 END	AS MarkWiad,
					CASE WHEN AD.ASP_WIADStatus	= 2 THEN 1 ELSE 0 END	as MarkActual,
					AD_DutyStatus				as IsActive,
					1							as isEditable,
					ISNULL(ad.AD_MasterDutyID,0)			AS MasterDutyId,
					CASE WHEN ASP_OverTwelveStatus = 0 THEN 9
						 WHEN ASP_OverTwelveStatus = 1 THEN -1
						 WHEN ASP_OverTwelveStatus = 2 THEN 1
						 WHEN ASP_OverTwelveStatus = 9 THEN 0
						 ELSE 9 END						 AS MarkOverTwelve,
					CASE WHEN ASP_UnderElevenBreakStatus = 0 THEN 0
						 WHEN ASP_UnderElevenBreakStatus IN (1,2) THEN 1
						 ELSE 0 END 			AS IsUnderElevenBreak,
					CASE WHEN ASP_UnderElevenBreakStatus = 0 THEN 0
						 WHEN ASP_UnderElevenBreakStatus = 2 THEN 1
						 ELSE 0 END 			AS IsUnderElevenBreakOverride,
					ASP_DutyTeamID				AS DutyTeamID,
					AD_DutyProgramID1			AS dutyProgramId,
					AD_DutyProgramID2			AS dutyProgramId2,
					AD_DutyProgramID3			AS dutyProgramId3,
					AD_DutyProgramID4			AS dutyProgramId4,
					AD_DutyProgramID5			AS dutyProgramId5,
					AD_DutyProgramID6			AS dutyProgramId6,
					case when CHARINDEX('#'+cast(AD_DutyProgramID1 as varchar)+'#', @TempLabelList, 1) > 0 then 1
					     when CHARINDEX('#'+cast(AD_DutyProgramID2 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID3 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID4 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID5 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID6 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 else 0 end as ActingFlag,
					CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.al_SchedulingTeamId
					 THEN 1 ELSE 0 END	IsDutyFromOtherTeam,
				    ISNULL(rft.PaymentTypeShortCode,'')  AS paymenttypename,
				   sp.UD_Displayname  AS DisplayName,
				   COALESCE(Asp_SortCode, aap_sortcode, spl.sortcode) AS sortcode,
				   scp.uc_eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   scp.uc_PartTimeEDP                             AS ContractedHours,
				   APD_AccPeriodDays						   AS accdays,
				   uc_manualedp                                AS manualedp,
				   ud_DisplayLastName                          AS DisplayLastName,
				   ud_DisplayFirstName                         AS DisplayFirstName,
				   st.schedulingTeamName                       AS schedulingTeamName,
				   ISNULL(st.signin,0)                         AS IsSigninAllowed,
				   ISNULL(st.signindays,0)                     AS Signindays,
				   case when ASP_SigninStatus is null then 0
				        when AD_StartTimeSec = ASP_SigninStartTime
					     AND AD_EndTimeSec = ASP_SigninEndTime then 2
					     ELSE  ISNULL(ASP_SigninStatus,0)  end       		   AS signin,
				   ISNULL(ASP_SigninINBuilding,0)							AS inbuilding,
				   case when ASP_SigninStatus = 1 and ( ASP_SigninStartTime <> AD_StartTimeSec
						OR ASP_SigninEndTime <> AD_EndTimeSec ) then 4
						when ASP_SigninStatus = 1 AND ASP_SigninINBuilding = 1 then 3
				        when ASP_SigninStatus = 1 AND ASP_SigninINBuilding <> 1 then 2
						when ASP_SigninStatus = 2 THEN 1
						else  1 end              AS ImageNameSignin,
				   case when ASP_SigninStatus = 1 and ( ASP_SigninStartTime <> AD_StartTimeSec
						OR ASP_SigninEndTime <> AD_EndTimeSec ) then 1
						when ASP_SigninStatus = 1 AND ASP_SigninINBuilding = 1 then 0
				        when ASP_SigninStatus = 1 AND ASP_SigninINBuilding <> 1 then 0
						else  1 end                            AS ActionNameForSignin,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   WHEN AD_DutyType IN (8,11,12)  THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#ebebeb'
				        ELSE spl.backgroundcolour END          AS PersonBackgroundColour,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#000000'
				        ELSE spl.fontcolour END                AS PersonFontColour,
				   ud_staffnumber                              AS staffnumber,
				   scp.uc_costcode                                AS CostCode,
				   ud_userid                                  AS staffid,
				   case when ASP_ChargingStatus = 5 AND ASP_ChargingTeamID = @pTeamID then 'None'
						when ASP_ChargingStatus = 4 AND ASP_ChargingTeamID = @pTeamID then 'Yellow'
						when ASP_ChargingStatus = 3 AND ASP_ChargingTeamID = @pTeamID then 'Green'
						when ASP_ChargingStatus = 2 AND ASP_ChargingTeamID = @pTeamID then 'Red'
						when ASP_ChargingStatus = 1 AND ASP_ChargingTeamID = @pTeamID then 'Blue'
						else NULL end AS TriangleColour,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.CountLeave  ELSE NULL END          AS CountLeave,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.Approved	ELSE NULL END			AS LeaveApproved,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.Deleted	ELSE NULL END			  AS LeaveDeleted,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.ShortNotice  ELSE NULL END         AS LeaveShortNotice,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.oversummer   ELSE NULL END         AS Leaveoversummer,
				   CASE WHEN ISNULL(LA.LeaveTypeID ,1) IN (1,2,6) THEN
				   LA.isOK  ELSE NULL END                  AS LeaveisOK,
				   ASP_LockRequestsStatus						AS ShowLock,
				   CASE WHEN ASP_RequestsStatus	= 1 THEN 'B'
						WHEN ASP_RequestsStatus	= 2 THEN 'G'
						WHEN ASP_RequestsStatus	= 3 THEN 'O'
						WHEN ASP_RequestsStatus	= 4 THEN 'Y'
					ELSE '' END AS LockIconColour,
				   ASP_RequestsCount							AS ReqCount,
				   ISNULL(ASP_EDPStatus,0)                      AS ShowEDPIcon,
				   APD_AccPeriodDuration                        AS WeekDuration,
				   CASE WHEN scp.uc_manualedp = 1
				        THEN APD_TotalOverTimeHRS
						WHEN scp.uc_manualedp = 0
						THEN APD_AccPeriodDuration - (ISNULL(scp.uc_PartTimeEDP,0) * 3600 )
						ELSE NULL END                           AS OverTimeHrs,
				   AccPeriodDuration                       AS TotalPlannedDuration,
				  CAST((DATEDIFF(day,AccPeriodStartDate,td.dDateTime)/7)+1 AS VARCHAR)+'/'+
				 CAST((DATEDIFF(day,acd.APD_AccPeriodStartDate,acd.APD_AccPeriodEndDate)+1)/7 AS VARCHAR) AS AccPeriod,
				   CASE WHEN AD_DutyType IN (8,12) AND ASP_LeaveType <> 7 THEN 0
				        WHEN ISNULL(ASP_DutyTeamID,0) > 0
					     AND ASP_DutyTeamID <> AL.AL_schedulingTeamId 
						 AND AD_DutyType NOT IN ( 7,9 )
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0  THEN 0
					    WHEN ISNULL(SPL.ishometeam,1) IN (0,2) and ISNULL(ASP_WIADStatus,0) = 0
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0 THEN 0
						WHEN ISNULL(SPL.ishometeam,1) = 1
						 AND ( ISNULL(ASP_WIADStatus,0) IN (1,2)) THEN 0
						WHEN NOT(td.dDateTime between spl.StartDate and spl.EndDate) THEN 0
						ELSE 1 END          AS EditDuty,
				   CASE WHEN spl.IsHomeTeam IN (0,2) THEN 0
				        WHEN spl.IsHometeam = 1 AND AD_DutyName <> 'U'
				         AND ISNULL(ASP_WIADStatus,0) = 0 THEN 0
						WHEN NOT(td.dDateTime between spl.StartDate and spl.EndDate) THEN 0
						 ELSE 1 END AS ShowWIAD,
				   CASE WHEN AD_DutyType NOT IN (8,11,12) AND ( SPL.IsHomeTeam = 1 OR ASP_WIADStatus IN (1,2)) THEN 2
						WHEN AD_DutyType IN (8,11,12) AND ASP_LeaveType NOT IN (1,2)
							 AND  ( SPL.IsHomeTeam = 1 OR ASP_WIADStatus IN (1,2))  THEN 2
						WHEN AD_DutyType IN (8,11,12) AND ASP_LeaveType IN (1,2)
							AND  ( SPL.IsHomeTeam = 1 OR ASP_WIADStatus IN (1,2))  THEN 1
						WHEN SPL.IsHomeTeam = 1 AND ISNULL(AD_AllocationsDutyID,0) = 0 THEN 2
						ELSE 0 END AS contextMenuClsName,
				   'AL' AS DisplayGrid,
				   CASE WHEN ( @NoOfWeeks = 1 AND APD_AccPeriodDutySummaryID IS NULL) THEN
				        SUM( CASE WHEN ISNULL(ASP_WIADStatus,0)=1 THEN 0
								  ELSE ISNULL(AD_Duration,0)-ISNULL(AD_DutyBreakTime,0) END) over (PARTITION BY AL.AL_WeekNumber, spl.ScheduledPersonID)
				   ELSE 0 end as CurrWeekDuration,
				   CASE WHEN ( @NoOfWeeks = 1 AND APD_AccPeriodDutySummaryID IS NULL) THEN
				        SUM( case when isnull(ASP_WIADStatus,0)=1 then 0
					              when isnull(AD_Duration,0) > 0 THEN 1 END ) over (PARTITION BY AL.AL_WeekNumber, spl.ScheduledPersonID)
				   ELSE 0 end as CurrWeekNoOfDays,
				   0 AS IsEpiredUSer,
			       ISNULL(LA.LeaveStartTime,0)                  AS LeaveStartTime,
			       ISNULL(LA.LeaveEndTime,0)                    AS LeaveEndTime,
				   CASE WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 0
				        THEN 0
						WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 1
						THEN 1
						ELSE  2 END  AS LeavePDL,
				   CASE WHEN SP.UD_FWANotes IS NOT NULL THEN 1 ELSE 0 END As FWANotesFlag,
				   UD_NetLogin					as NetLogin,
				   CASE WHEN ISNULL(LA.LeaveTypeID,0) NOT IN (3,4,5) THEN  LA.ID ELSE 0 END LeaveID,
				   LA.IsAgreed,
				   AD_IsNeedCovering			as IsNeedCovering,
				   AD_IsOverrideOver12			as IsOverrideOver12,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   AD_DutyType,
				   ASP_LeaveType,
				   CASE WHEN spl.IsHomeTeam IN (0,2) AND spl.IsAvailable = 0 AND AAP.AAP_AllocationsAPID IS NULL THEN 0 ELSE 1 END AS AddSPExclFilter
			  FROM Allocations_ARCH AS AL (nolock)
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			 INNER JOIN @SPFilterList SFL on SFL.scheduledpersonid = CASE WHEN @SPFlag = 1
																		  THEN spl.ScheduledPersonID
																		  ELSE SFL.scheduledpersonid END
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL_WeekNumber
			 INNER JOIN UserDetails AS sp (nolock) ON UD_UserID = spl.ScheduledPersonID
			 LEFT JOIN 	@TempAllocations AD on AL.AL_SchedulingTeamID = AD.AL_SchedulingTeamID
							AND spl.ScheduledPersonID = AD.ASP_SchedulingPersonID
						     AND TD.ixYearWeek = AD.WeekNumber
							 AND td.ixDayInWeek = AD.ASP_iDay 
							 /* AL.AL_SchedulingTeamID = AD.AL_SchedulingTeamID
							 and spl.ScheduledPersonID = AD.ASP_SchedulingPersonID
							 AND TD.dDateTime = ASP_DutyDate */
		    LEFT JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0
			                                                              THEN ASP_DutyTeamID
																		  ELSE spl.TeamID END
			LEFT JOIN @FreeLancerList FRL ON FRL.scheduledpersonid =  spl.ScheduledPersonID
			LEFT JOIN UserConfigs scp (nolock) ON scp.UC_UserID = spl.ScheduledPersonID
							 AND AD_DutyDate BETWEEN scp.UC_StartDate AND scp.UC_EndDate
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = scp.UC_AccGroupID
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = scp.UC_PaymentTypeID
			LEFT JOIN REF_MasterDutyColours mdc (nolock) ON mdc.masterdutycolourid = AD_DutyColourID
			LEFT JOIN LeaveApplications LA  (nolock) ON TD.dDateTime = LA.dDate  AND LA.schedulingpersonid = spl.ScheduledPersonID
			                                        AND LA.Deleted = 0
													AND LA.LeaveTypesID > 0
			LEFT JOIN AllocationsAddPersons AAP on AL.AL_AllocationsID = AAP.AAP_AllocationsID
														  AND spl.ScheduledPersonID = AAP.AAP_SchedulingPersonID
														  AND td.ixDayInWeek = AAP.AAP_iDay
														  AND AAP.AAP_Status = 1
			LEFT JOIN AccPeriodROTADurationSummary ACR on ACR.ScheduledPersonID = spl.ScheduledPersonID
													AND TD.dDateTime between ACR.AccPeriodStartDate and ACR.AccPeriodEndDate
			LEFT JOIN AccPeriodDutySummary ACD on ACD.APD_ScheduledPersonID = spl.ScheduledPersonID
													AND TD.dDateTime between APD_AccPeriodStartDate and APD_AccPeriodEndDate
		     WHERE td.dDateTime between spl.startdate and spl.EndDate
			   AND SPL.scheduledType = 1
			   AND TD.dDateTime between @pStartDate and @pEndDate
			   AND AL.AL_SchedulingTeamID = @pteamId
			   AND NOT EXISTS ( SELECT 1
								  FROM AllocationsDelPersons_ARCH ADP
								 WHERE ADP_AllocationsID = AL.AL_AllocationsID
								   AND spl.ScheduledPersonID = ADP.ADP_SchedulingPersonID)
			   AND @ArchiveDataFlag = 1
		) AL LEFT JOIN
		(
			select schedulingpersonid,
			       weeknumber,
				   iday,
				   min(isapproved) isapproved,
				   max(breachtype) maxbreachtype
			FROM (
			SELECT wtd.schedulingTeamId,
			       wtd.schedulingpersonid,
				   wtd.breachtype,
				   td.ixYearWeek weeknumber,
				   td.ixDayInWeek iday,
				   wtd.isapproved,
				   TD.dDateTime
			  FROM working_time_directive WTD (nolock)
			 INNER JOIN TimeDimension TD (nolock) ON td.dDateTime between wtd.StartDate and wtd.enddate
			 INNER JOIN @SPFilterList SPFL on SPFL.ScheduledPersonID = CASE WHEN @SPFlag = 1
																			THEN WTD.SchedulingPersonID
																			ELSE SPFL.ScheduledPersonID END
			 WHERE StartDate <= @pEndDate
			   AND EndDate >= @pStartDate
			   AND wtd.isapproved <> 2
               AND 1 = CASE WHEN @ShowOnlyUnAllocatedDuty = 1 THEN 2 ELSE 1 END
			  ) FD GROUP BY schedulingpersonid, weeknumber, iday
		) WTD ON WTD.schedulingpersonid = AL.schedulingpersonid
		     and WTD.weeknumber = AL.WeekNumber
			 AND WTD.iday = AL.iday
		WHERE AL.AddSPExclFilter = 1
	  UNION ALL
		( SELECT NULL                     AS StaffNumber,
			   AD_DutyName                        AS DutyName,
			   AD_Duration                        AS Duration,
			   AL_WeekNumber                      AS WeekNumber,
			   AD_iDay                            AS iDay,
			   AD_StartTimeSec                    AS StartTime,
			   AD_EndTimeSec                      AS EndTime,
			   NULL		                          AS SortCode,
			   CASE WHEN (AD_Comments IS NULL) or (AD_Comments = '') THEN 0 ELSE 1 END AS DutyComments,
			   0                  AS PersonComments,
			   0                  AS MarkedOvertime,
			   0                  AS MarkedSickness,
			   1                     AS UnAllocated,
			   AL_AllocationsID                              AS ID,
			   AL_SchedulingTeamID                AS SchedulingTeamId,
			   0              AS SchedulingPersonID,
			   FORMAT(AD_DutyDate, 'yyyy-MM-dd')  AS DutyDate,
			   AL_Status                     AS isPublished,
			   1                      AS IsHomeTeam,
			   0                        AS MarkWiad,
			   0                      AS MarkActual,
			   AD_isAttention                     AS isAttentionClsName,
			   AD_isRequest                       AS isRequest,
			   AD_DutyBreakTime                   AS dutyBreakTime,
			   AD_DutyColourID                    AS dutyColorId,
			   AD_MasterDutyID                    AS MasterDutyId,
			   AD_DutyStatus                        AS isActive,
			   1                      AS isEditable,
			   NULL                 AS pay,
			   NULL                     AS DisplayName,
			   NULL                 AS DisplayLastName,
			   NULL                AS DisplayFirstName,
			   st.schedulingTeamName              AS schedulingTeamName,
			   0                 AS IsSigninAllowed,
			   0                      AS Signindays,
			   0                             AS EFT,
			   NULL                          AS ACC,
			   0                 AS ContractedHours,
			   0           AS AccDays,
			   0                     AS OverTimeHrs,
			   0                       AS ManualEDP,
			   0                          AS signin,
			   0                      AS inbuilding,
               0             AS ActionNameForSignin,
			   0                 AS ImageNameSignin,
			   NULL                AS ColourBackground,
			   NULL                      AS ColourFont,
			   NULL          AS PersonBackgroundColour,
			   NULL                AS PersonFontColour,
			   '00.00'   AS WeekDuration,
			   NULL                       AS AccPeriod,
			   NULL                        AS CostCode,
			   0                         AS StaffID,
			   NULL                  AS TriangleColour,
			   0                      AS CountLeave,
			   0                   AS LeaveApproved,
			   0                    AS LeaveDeleted,
			   0                AS LeaveShortNotice,
			   0                 AS Leaveoversummer,
			   0                       AS LeaveisOK,
			   1                        AS EditDuty,
			   0,
			   NULL		as LockIconColour,
			   0		as ReqCount,
			   0                     AS ShowEDPIcon,
			   0                        AS ShowWIAD,
			   0                               AS WTDBreachClassName,
			   0              AS contextMenuClsName,
			   'UL'                     AS DisplayGrid,
			   0                  AS MarkOverTwelve,
			   0              AS IsUnderElevenBreak,
			   0     AS IsUnderElevenBreakOverride,
			   0 			          AS DutyTeamID,
			   0             AS IsDutyFromOtherTeam,
			   AD_DutyProgramID1                   AS dutyProgramId,
			   count(AD_DutyName) over ( partition by ad_dutydate, ad_dutyname) AS DutyInstances,
			   0                  AS LeaveStartTime,
			   0                    AS LeaveEndTime,
			   0                        AS LeavePDL,
			   0                    AS FWANotesFlag,
			   NULL                        AS NetLogin,
			   0                         AS LeaveID,
			   AD_DutyProgramID2                  AS dutyProgramId2,
			   AD_DutyProgramID3                  AS dutyProgramId3,
			   AD_DutyProgramID4                  AS dutyProgramId4,
			   AD_DutyProgramID5                  AS dutyProgramId5,
			   AD_DutyProgramID6                  AS dutyProgramId6,
			   0                        AS IsAgreed,
			   AD_IsNeedCovering                  AS IsNeedCovering,
			   case when CHARINDEX('#'+cast(AD_DutyProgramID1 as varchar)+'#', @TempLabelList, 1) > 0 then 1
					     when CHARINDEX('#'+cast(AD_DutyProgramID2 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID3 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID4 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID5 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID6 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 else 0 end                       AS ActingFlag,
			   0            AS TotalPlannedDuration,
			   AD_IsOverrideOver12                AS IsOverrideOver12,
			   AD_AllocationsDutyID				AS AllocationsDutyID,
			   0								  AS AllocationsSPID
			  FROM Allocations AL
			  INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
			  INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek AND TD.ixDayInWeek = AD.AD_iDay
			  INNER JOIN schedulingTeams st on st.schedulingTeamId = al.AL_SchedulingTeamID
			  WHERE dDateTime between @pStartDate AND @pEndDate
			    AND AL_SchedulingTeamID = @pteamId
			    AND AD_DutyStatus = 0
				AND AD_DutyType <> 10
				AND @AllocDataFlag = 1
				AND @SPFlag = 0
			UNION ALL
		SELECT NULL                     AS StaffNumber,
			   AD_DutyName                        AS DutyName,
			   AD_Duration                        AS Duration,
			   AL_WeekNumber                      AS WeekNumber,
			   AD_iDay                            AS iDay,
			   AD_StartTimeSec                    AS StartTime,
			   AD_EndTimeSec                      AS EndTime,
			   NULL		                          AS SortCode,
			   CASE WHEN (AD_Comments IS NULL) or (AD_Comments = '') THEN 0 ELSE 1 END AS DutyComments,
			   0                  AS PersonComments,
			   0                  AS MarkedOvertime,
			   0                  AS MarkedSickness,
			   1                     AS UnAllocated,
			   AL_AllocationsID                              AS ID,
			   AL_SchedulingTeamID                AS SchedulingTeamId,
			   0              AS SchedulingPersonID,
			   FORMAT(AD_DutyDate, 'yyyy-MM-dd')  AS DutyDate,
			   AL_Status                     AS isPublished,
			   1                      AS IsHomeTeam,
			   0                        AS MarkWiad,
			   0                      AS MarkActual,
			   AD_isAttention                     AS isAttentionClsName,
			   AD_isRequest                       AS isRequest,
			   AD_DutyBreakTime                   AS dutyBreakTime,
			   AD_DutyColourID                    AS dutyColorId,
			   AD_MasterDutyID                    AS MasterDutyId,
			   AD_DutyStatus                        AS isActive,
			   1                      AS isEditable,
			   NULL                 AS pay,
			   NULL                     AS DisplayName,
			   NULL                 AS DisplayLastName,
			   NULL                AS DisplayFirstName,
			   st.schedulingTeamName              AS schedulingTeamName,
			   0                 AS IsSigninAllowed,
			   0                      AS Signindays,
			   0                             AS EFT,
			   NULL                          AS ACC,
			   0                 AS ContractedHours,
			   0           AS AccDays,
			   0                     AS OverTimeHrs,
			   0                       AS ManualEDP,
			   0                          AS signin,
			   0                      AS inbuilding,
               0             AS ActionNameForSignin,
			   0                 AS ImageNameSignin,
			   NULL                AS ColourBackground,
			   NULL                      AS ColourFont,
			   NULL          AS PersonBackgroundColour,
			   NULL                AS PersonFontColour,
			   '00.00'   AS WeekDuration,
			   NULL                       AS AccPeriod,
			   NULL                        AS CostCode,
			   0                         AS StaffID,
			   NULL                  AS TriangleColour,
			   0                      AS CountLeave,
			   0                   AS LeaveApproved,
			   0                    AS LeaveDeleted,
			   0                AS LeaveShortNotice,
			   0                 AS Leaveoversummer,
			   0                       AS LeaveisOK,
			   1                        AS EditDuty,
			   0,
			   NULL		as LockIconColour,
			   0		as ReqCount,
			   0                     AS ShowEDPIcon,
			   0                        AS ShowWIAD,
			   0                               AS WTDBreachClassName,
			   0              AS contextMenuClsName,
			   'UL'                     AS DisplayGrid,
			   0                  AS MarkOverTwelve,
			   0              AS IsUnderElevenBreak,
			   0     AS IsUnderElevenBreakOverride,
			   0 			          AS DutyTeamID,
			   0             AS IsDutyFromOtherTeam,
			   AD_DutyProgramID1                   AS dutyProgramId,
			   count(AD_DutyName) over ( partition by ad_dutydate, ad_dutyname) AS DutyInstances,
			   0                  AS LeaveStartTime,
			   0                    AS LeaveEndTime,
			   0                        AS LeavePDL,
			   0                    AS FWANotesFlag,
			   NULL                        AS NetLogin,
			   0                         AS LeaveID,
			   AD_DutyProgramID2                  AS dutyProgramId2,
			   AD_DutyProgramID3                  AS dutyProgramId3,
			   AD_DutyProgramID4                  AS dutyProgramId4,
			   AD_DutyProgramID5                  AS dutyProgramId5,
			   AD_DutyProgramID6                  AS dutyProgramId6,
			   0                        AS IsAgreed,
			   AD_IsNeedCovering                  AS IsNeedCovering,
			   case when CHARINDEX('#'+cast(AD_DutyProgramID1 as varchar)+'#', @TempLabelList, 1) > 0 then 1
					     when CHARINDEX('#'+cast(AD_DutyProgramID2 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID3 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID4 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID5 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AD_DutyProgramID6 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 else 0 end                       AS ActingFlag,
			   0            AS TotalPlannedDuration,
			   AD_IsOverrideOver12                AS IsOverrideOver12,
			   AD_AllocationsDutyID				AS AllocationsDutyID,
			   0								  AS AllocationsSPID
			  FROM Allocations_ARCH AL
			  INNER JOIN AllocationsDuties_ARCH AD on AL_AllocationsID = AD_AllocationsID
			  INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek AND TD.ixDayInWeek = AD.AD_iDay
			  INNER JOIN schedulingTeams st on st.schedulingTeamId = al.AL_SchedulingTeamID
			  WHERE dDateTime between @pStartDate AND @pEndDate
			    AND AL_SchedulingTeamID = @pteamId
			    AND AD_DutyStatus = 0
				AND AD_DutyType <> 10
				AND @ArchiveDataFlag = 1
				AND @SPFlag = 0
			)


	  END


END