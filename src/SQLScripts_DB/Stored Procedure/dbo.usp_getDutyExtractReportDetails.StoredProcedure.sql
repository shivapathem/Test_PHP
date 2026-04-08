USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_getDutyExtractReportDetails]    Script Date: 03/04/2026 14:30:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 09-06-2022
-- Description:	Used to get details related to duty extract report
-- =============================================
CREATE OR ALTER                        PROCEDURE [dbo].[usp_getDutyExtractReportDetails]
	@weekFrom VARCHAR(10),
	@weekTo VARCHAR(10),
	@ExtractDays VARCHAR(10),
	@extractFilter INT,
	@includeLeave INT,
	@includeJobData INT,
	@reportType INT,
	@extractGroup1 INT,
	@extractGroup2 INT,
	@staffNumber INT,
	@staffNumberVal VARCHAR(MAX),
	@extractJob INT,
	@extractJobVal VARCHAR(100),
	@extractProgramme INT,
	@extractProgrammeVal VARCHAR(100),
	@extractContact INT,
	@extractContactVal VARCHAR(100),
	@extractLocation INT,
	@extractLocationVal VARCHAR(100),
	@extractDuty INT,
	@extractDutyVal VARCHAR(100),
	@extractFilterBy INT,
	@extractFilterByCond INT,
	@extractFilterByCondVal INT,
	@extractHistoryData INT,
	@schedulingTeam VARCHAR(10),
	@extractJobProgramme INT,
	@extractJobProgrammeVal VARCHAR(100),
	@sortingCol VARCHAR(63) = '1',
	@sortingType VARCHAR(7) = 'asc',
	@userID INT = NULL
AS
BEGIN


	SET NOCOUNT ON;

	DECLARE @queryCond VARCHAR(MAX) = '', 
	        @query VARCHAR(MAX) = '', 
			@grpByColName VARCHAR(100) = '', 
			@grpByColNameList VARCHAR(100) = '', 
			@orderByStr VARCHAR(MAX) = '',
			@defaultColsList varchar(MAX) = 'SP.ud_DisplayName as DisplayName, SP.UD_StaffNumber as StaffNumber, isNull(AL.sortcode, SPTL.sortcode) AS sortcode, 
	AL.DutyDate, AL.WeekNumber, AL.iDay, AL.DutyName, AL.StartTime, AL.EndTime, Al.Duration, Al.dutyBreakTime,PG.Programme,
	PG2.Programme as DutyLabel2, PG3.Programme  as DutyLabel3,PG4.Programme  as DutyLabel4, 
	PG5.Programme as DutyLabel5, PG6.Programme  as DutyLabel6,
	MJ.JobName, MJ.Location, JPG.Programme JobLabel, MJ.Contact,MJ.StartTime JobStartTime,  MJ.EndTime JobEndTime, AL.DutyComments,
	ST.schedulingTeamName as HomeTeam, LT.[Description] as DisplayAllocName, AL.PersonComments,'''' History' 

	DECLARE @dutyFilterStr VARCHAR(MAX) = '', 
			@sortCodeFilter VARCHAR(MAX) = '', 
			@staffName VARCHAR(MAX) = '',
			@costCode VARCHAR(MAX) = '', 
			@skillName VARCHAR(MAX) = '', 
			@dutyLable VARCHAR(MAX) = '',
			@dutyLable2 VARCHAR(MAX) = '',
			@dutyLable3 VARCHAR(MAX) = '',
			@dutyLable4 VARCHAR(MAX) = '',
			@dutyLable5 VARCHAR(MAX) = '',
			@dutyLable6 VARCHAR(MAX) = '';

	DECLARE @sortCode BIT = 0, 
	        @andMatch BIT = 0, 
			@sortOrder BIT = 0,
			@ArchiveDataFlag  BIT = 0,
			@AllocDataFlag	BIT = 0;

	DECLARE @TempTeam TABLE ( TeamID INT);



	IF EXISTS ( select top 1 TD.ID
				  from ArchivedWeeks AW
				 inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
				 where td.ixYearWeek between @weekFrom and @weekTo
			   )
		SET @ArchiveDataFlag = 1

	IF EXISTS (  select top 1 TD1.ID
				   from TimeDimension TD1
				  WHERE TD1.ixYearWeek between @weekFrom and @weekTo
				    AND NOT EXISTS
								 (
								  select TD.ID
								   from ArchivedWeeks AW
								   inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
								   where TD1.ID = TD.ID
								 )
				)
		 SET @AllocDataFlag = 1

	IF(@schedulingTeam > 0)
	BEGIN
		INSERT INTO @TempTeam VALUES ( @schedulingTeam )
	END
	ELSE
	BEGIN

	   INSERT INTO @TempTeam    
	   select DISTINCT st.SchedulingTeamId
	   	FROM schedulingTeams st (nolock)
		where 
		  EXISTS (
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				WHERE UR.UR_SchedulingTeamID = st.SchedulingTeamId
				AND UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND UR.UR_UserID = @UserID 
				AND RR.RoleName IN ('Advanced Reports', 'Scheduling Team Admin')
			UNION
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				INNER join schedulingTeams stf2 on stf2.divisionId = UR.UR_DivisionId
				WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND RR.RoleName = 'Area Admin'
				AND stf2.schedulingTeamId = st.schedulingTeamId
				AND UR.UR_UserID = @UserID 
			UNION
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND RR.RoleName = 'System Admin'
				AND UR.UR_UserID = @UserID 
			)
			and st.schedulingTeamName not in ('Archive','TO Archive','Freelancers','Other BBC')

	END

	SELECT * 
	  INTO #TempDuty 
	 FROM  (
			select  AL_AllocationsID,
					al.AL_SchedulingTeamID,
					AL_WeekNumber as WeekNumber,
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
							THEN NULL 
							ELSE AD_Comments END AS AD_Comments,
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
					ASP_Comments,
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
					AD_IsDutyEdited,
					ASP_UnderElevenComments,
					ASP_CreatedBy,
					ASP_CreatedDate,
					ASP_UpdatedBy,
					ASP_UpdatedDate,
					0 AS AAP_AllocationsAPID
				FROM Allocations AS AL (nolock)
				INNER JOIN @TempTeam TT ON TT.TeamID = AL_SchedulingTeamID
				INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
				INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				WHERE AL_WeekNumber between @weekFrom AND @weekTo
				AND @AllocDataFlag = 1
				AND AL.AL_Status <> 9
				UNION All
				select AL.AL_AllocationsID,
					al.AL_SchedulingTeamID,
					AL.AL_WeekNumber as WeekNumber,
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
							THEN NULL ELSE AD_Comments END AS AD_Comments,
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
					AA.AAP_Comments ASP_Comments,
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
					AD_IsDutyEdited,
					ASP_UnderElevenComments,
					AA.AAP_CreatedBy ASP_CreatedBy,
					AA.AAP_CreatedDate ASP_CreatedDate,
					AA.AAP_UpdatedBy ASP_UpdatedBy,
					AA.AAP_UpdatedDate ASP_UpdatedDate,
					AA.AAP_AllocationsAPID
				FROM Allocations AS AL (nolock)
				INNER JOIN @TempTeam TT ON TT.TeamID = AL_SchedulingTeamID
				INNER JOIN AllocationsAddPersons AA on AL_AllocationsID = AAP_AllocationsID
				INNER JOIN AllocationsScheduledPersons ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
				INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				INNER JOIN Allocations ALA ON ALA.AL_AllocationsID = AD_AllocationsID
				WHERE AL.AL_WeekNumber between @weekFrom AND @weekTo
				AND ALA.AL_Status <> 9
				AND @AllocDataFlag = 1
				AND AL.AL_Status <> 9
			) FD

		SELECT * 
		  INTO #TempDutyARCH 
		  FROM (
			SELECT  AL_AllocationsID,
					AL_WeekNumber as WeekNumber,
					al.AL_SchedulingTeamID,
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
							THEN NULL 
							ELSE AD_Comments END AS AD_Comments,
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
					ASP_Comments,
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
					AD_IsDutyEdited,
					ASP_UnderElevenComments,
					ASP_CreatedBy,
					ASP_CreatedDate,
					ASP_UpdatedBy,
					ASP_UpdatedDate,
					0 AS AAP_AllocationsAPID
				FROM Allocations_ARCH AS AL (nolock)
				INNER JOIN @TempTeam TT ON TT.TeamID = AL_SchedulingTeamID
				INNER JOIN AllocationsScheduledPersons_ARCH ASP on AL_AllocationsID = ASP_AllocationsID
				INNER JOIN AllocationsDuties_ARCH AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				WHERE AL_WeekNumber between @weekFrom AND @weekTo
				AND @ArchiveDataFlag = 1
				AND AL.AL_Status <> 9
				UNION All
				select AL.AL_AllocationsID,
					AL.AL_WeekNumber as WeekNumber,
					al.AL_SchedulingTeamID,
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
							THEN NULL ELSE AD_Comments END AS AD_Comments,
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
					AA.AAP_Comments ASP_Comments,
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
					AD_IsDutyEdited,
					ASP_UnderElevenComments,
					AA.AAP_CreatedBy ASP_CreatedBy,
					AA.AAP_CreatedDate ASP_CreatedDate,
					AA.AAP_UpdatedBy ASP_UpdatedBy,
					AA.AAP_UpdatedDate ASP_UpdatedDate,
					AA.AAP_AllocationsAPID
				FROM Allocations_ARCH AS AL (nolock)
				INNER JOIN @TempTeam TT ON TT.TeamID = AL_SchedulingTeamID
				INNER JOIN AllocationsAddPersons_ARCH AA on AL_AllocationsID = AAP_AllocationsID
				INNER JOIN AllocationsScheduledPersons_ARCH ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
				INNER JOIN AllocationsDuties_ARCH AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				INNER JOIN Allocations_ARCH ALA ON ALA.AL_AllocationsID = AD_AllocationsID
				WHERE AL.AL_WeekNumber between @weekFrom AND @weekTo
				AND ALA.AL_Status <> 9
				AND AL.AL_Status <> 9
				AND @ArchiveDataFlag = 1
			) FD


	 SELECT al.AL_SchedulingTeamID,
			sl.ScheduledPersonID,
			td.dDateTime,
			IsHomeTeam,
			IsAvailable,
			AL_AllocationsID,
			ixDayInWeek,
			AL_WeekNumber
	   INTO #TempSP
	   FROM Allocations AL WITH(NOLOCK)
	  INNER JOIN @TempTeam TT ON TT.TeamID = AL_SchedulingTeamID
	  INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
	  INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
	  WHERE td.dDateTime between sl.startdate and sl.EndDate
		AND SL.scheduledType = 1
		AND TD.ixYearWeek between  @weekFrom AND @weekTo		
		AND @AllocDataFlag = 1
		AND AL.AL_Status <> 9
	
	 SELECT al.AL_SchedulingTeamID,
			sl.ScheduledPersonID,
			td.dDateTime,
			IsHomeTeam,
			IsAvailable,
			AL_AllocationsID,
			ixDayInWeek,
			AL_WeekNumber
	   INTO #TempSPARCH
	  FROM  Allocations_ARCH AL WITH(NOLOCK)
	  INNER JOIN @TempTeam TT ON TT.TeamID = AL_SchedulingTeamID
	  INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
	  INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
	  WHERE td.dDateTime between sl.startdate and sl.EndDate
	    AND SL.scheduledType = 1
		AND TD.ixYearWeek between  @weekFrom AND @weekTo
		AND @ArchiveDataFlag = 1
		AND AL.AL_Status <> 9


	SELECT  AllocationID,
			AllocationSPID,
			AllocationDutyID,
			StaffNumber,
			DutyName,
			Duration,
			WeekNumber,
			iDay,
			 StartTime ,
			 EndTime,
			 ActingGrade,
			 SortCode ,
			 LeaveID,
			 ManualERR,
			 DutyComments,
			 PersonComments,
			 BaseCode,
			 BackColour,
			 FontColour,
			 AdhocDuty,
			 MarkedOvertime ,
			 MarkedPTExtraDay,
			 MarkedCompLeave,
			MarkedSickness,
			 ManualOTAmount,
			 ManualOTExcBreaksAmount,
			 UnAllocated,
			 SchedulingTeamId,
			 SchedulingPersonID,
			 DutyDate,
			 StartDate,
			 EndDate,
			 IsHomeTeam ,
			 MarkWiad,
			 MarkActual,
			 aftermidnight,
			  isAttention,
			 isRequest,
			 dutyProgramId,
			 dutyProgramId2,
			 dutyProgramId3,
			 dutyProgramId4,
			 dutyProgramId5,
			 dutyProgramId6,
			 dutyBreakTime,
			 dutyColorId,
			 MannualOThours,
			 isEdited,
			 MasterDutyId,
			 isActive,
			 isActiveDuty,
			 isEditable,
			 DutyTeamID ,
			 PlannedDuration,  
			 MarkOverTwelve,
			 OverTwelveHrs,
			 IsOverseasOverTwelve,
			 IsUnderElevenBreak,
			 CalculatedUnderElevenHrs,
			 IsUnderElevenBreakOverride,
			 OverrideUnderElevenHrs,
			 UnderElevenComment,
			 LeaveStartTime,
			 LeaveENDTime  ,
			 CreatedBy,
			 CreatedDate,
			 UpdatedBy,
			 UpdatedDate
	 INTO #TempAllocations
	 FROM (
	 SELECT AL.AL_AllocationsID	AS AllocationID,
			ASP_AllocationsSPID AS AllocationSPID,
			AD_AllocationsDutyID AS AllocationDutyID,
			NULL 				AS StaffNumber ,
			CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
					WHEN AD.AD_DutyType IN (8,11,12)
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
					ELSE AD.AD_DutyName
			END AS DutyName,
			CASE WHEN AD_DutyType IN (8,11,12)
				THEN ISNULL(ASP_LeaveDuration,0)
				ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
			AL.AL_WeekNumber AS WeekNumber,
			AL.ixDayInWeek AS iDay,
			ISNULL(AD_StartTimeSec,0) AS StartTime ,
			ISNULL(AD_EndTimeSec,0) AS EndTime,
			NULL	AS ActingGrade,
			ASP_SortCode		AS SortCode ,
			CASE WHEN AD.AD_DutyType IN (8,11,12) AND ASP_LeaveType IN (1,2) 
				THEN 1 ELSE 0 END	AS LeaveID,
			NULL AS ManualERR,
			AD.AD_Comments AS DutyComments,
			AD.ASP_Comments AS PersonComments,
			0				 AS BaseCode,
			NULL AS BackColour,
			NULL AS FontColour,
	 		CASE WHEN AD_DutyType = 6 THEN 1 ELSE 0 END AS AdhocDuty,
			ASP_MarkedOverTime	AS MarkedOvertime ,
			0					AS MarkedPTExtraDay,
			0					AS MarkedCompLeave,
			CASE WHEN ASP_LeaveType IN (3,4,5) THEN 1 ELSE 0 END AS	MarkedSickness,
			0				AS ManualOTAmount,
			0				AS ManualOTExcBreaksAmount,
			1				AS UnAllocated,
			AL.AL_SchedulingTeamID	AS SchedulingTeamId,
			AL.ScheduledPersonID	AS SchedulingPersonID,
			AL.dDateTime AS DutyDate,
			AD.AD_DutyStartTimeLocal AS StartDate,
			AD.AD_DutyEndTimeLocal AS EndDate,
			AL.IsHomeTeam AS IsHomeTeam ,
			CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END AS MarkWiad,
			CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END AS MarkActual,
			CASE WHEN DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 THEN 1 ELSE 0 END AS aftermidnight,
			AD_isAttention AS  isAttention,
			AD.AD_isRequest AS isRequest,
			AD.AD_DutyProgramID1 AS dutyProgramId,
			AD.AD_DutyProgramID2 AS dutyProgramId2,
			AD.AD_DutyProgramID3 AS dutyProgramId3,
			AD.AD_DutyProgramID4 AS dutyProgramId4,
			AD.AD_DutyProgramID5 AS dutyProgramId5,
			AD.AD_DutyProgramID6 AS dutyProgramId6,
			AD.AD_DutyBreakTime AS dutyBreakTime,
			AD.AD_DutyColourID AS dutyColorId,
			ASP_OverTimeHours AS MannualOThours,
			AD.AD_IsDutyEdited AS isEdited,
			AD.AD_MasterDutyID AS MasterDutyId,
			CASE WHEN AD_DutyStatus = 9 THEN 0 ELSE 1 END AS isActive,
			1 AS isActiveDuty,
			1 AS isEditable,
			NULL AS DutyTeamID ,
			AD.AD_PlannedDuration AS PlannedDuration,  
			AD.ASP_OverTwelveStatus as MarkOverTwelve,
			AD.ASP_OverTwelveHrs AS OverTwelveHrs,
			AD.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
			CASE WHEN AD.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
			AD.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
			CASE WHEN AD.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
			AD.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
			AD.ASP_UnderElevenComments	AS UnderElevenComment,
			ASP_LeaveStartTimeSec	AS LeaveStartTime,
			ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
			ASP_CreatedBy AS CreatedBy,
			ASP_CreatedDate AS CreatedDate,
			ASP_UpdatedBy AS UpdatedBy,
			ASP_UpdatedDate AS UpdatedDate,
			CASE WHEN AL.IsHomeTeam IN (0, 2) AND AL.IsAvailable = 0 AND AAP.AAP_AllocationsAPID IS NULL
				 THEN 0 
				 ELSE 1 
				 END AS AddSPExclFilter	
		FROM #TempSP AL
		LEFT JOIN #TempDuty	AD on AL.AL_SchedulingTeamID = AD.AL_SchedulingTeamID
							 and AL.ScheduledPersonID = AD.ASP_SchedulingPersonID
							 AND AL.dDateTime = ASP_DutyDate
		LEFT JOIN AllocationsAddPersons AAP on AL.AL_AllocationsID = AAP.AAP_AllocationsID
										   AND AL.ScheduledPersonID = AAP.AAP_SchedulingPersonID
										   AND AL.ixDayInWeek = AAP.AAP_iDay
		WHERE NOT EXISTS ( SELECT 1
							 FROM AllocationsDelPersons ADP
							WHERE ADP_AllocationsID = AL.AL_AllocationsID
							  AND AL.ScheduledPersonID = ADP.ADP_SchedulingPersonID
						 )					
	UNION ALL
	 SELECT AL.AL_AllocationsID	AS AllocationID,
			ASP_AllocationsSPID AS AllocationSPID,
			AD_AllocationsDutyID AS AllocationDutyID,
			NULL 				AS StaffNumber ,
			CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
					WHEN AD.AD_DutyType IN (8,11,12)
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
					ELSE AD.AD_DutyName
			END AS DutyName,
			CASE WHEN AD_DutyType IN (8,11,12)
				THEN ISNULL(ASP_LeaveDuration,0)
				ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
			AL.al_WeekNumber AS WeekNumber,
			AL.ixDayInWeek AS iDay,
			ISNULL(AD_StartTimeSec,0) AS StartTime ,
			ISNULL(AD_EndTimeSec,0) AS EndTime,
			NULL	AS ActingGrade,
			ASP_SortCode		AS SortCode ,
			CASE WHEN AD.AD_DutyType IN (8,11,12) AND ASP_LeaveType IN (1,2) 
				THEN 1 ELSE 0 END	AS LeaveID,
			NULL AS ManualERR,
			AD.AD_Comments AS DutyComments,
			AD.ASP_Comments AS PersonComments,
			0				 AS BaseCode,
			NULL AS BackColour,
			NULL AS FontColour,
	 		CASE WHEN AD_DutyType = 6 THEN 1 ELSE 0 END AS AdhocDuty,
			ASP_MarkedOverTime	AS MarkedOvertime ,
			0					AS MarkedPTExtraDay,
			0					AS MarkedCompLeave,
			CASE WHEN ASP_LeaveType IN (3,4,5) THEN 1 ELSE 0 END AS	MarkedSickness,
			0				AS ManualOTAmount,
			0				AS ManualOTExcBreaksAmount,
			1				AS UnAllocated,
			AL.AL_SchedulingTeamID	AS SchedulingTeamId,
			AL.ScheduledPersonID	AS SchedulingPersonID,
			AL.dDateTime AS DutyDate,
			AD.AD_DutyStartTimeLocal AS StartDate,
			AD.AD_DutyEndTimeLocal AS EndDate,
			AL.IsHomeTeam AS IsHomeTeam ,
			CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END AS MarkWiad,
			CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END AS MarkActual,
			CASE WHEN DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 THEN 1 ELSE 0 END AS aftermidnight,
			AD_isAttention AS  isAttention,
			AD.AD_isRequest AS isRequest,
			AD.AD_DutyProgramID1 AS dutyProgramId,
			AD.AD_DutyProgramID2 AS dutyProgramId2,
			AD.AD_DutyProgramID3 AS dutyProgramId3,
			AD.AD_DutyProgramID4 AS dutyProgramId4,
			AD.AD_DutyProgramID5 AS dutyProgramId5,
			AD.AD_DutyProgramID6 AS dutyProgramId6,
			AD.AD_DutyBreakTime AS dutyBreakTime,
			AD.AD_DutyColourID AS dutyColorId,
			ASP_OverTimeHours AS MannualOThours,
			AD.AD_IsDutyEdited AS isEdited,
			AD.AD_MasterDutyID AS MasterDutyId,
			CASE WHEN AD_DutyStatus = 9 THEN 0 ELSE 1 END AS isActive,
			1 AS isActiveDuty,
			1 AS isEditable,
			NULL AS DutyTeamID ,
			AD.AD_PlannedDuration AS PlannedDuration,  
			AD.ASP_OverTwelveStatus as MarkOverTwelve,
			AD.ASP_OverTwelveHrs AS OverTwelveHrs,
			AD.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
			CASE WHEN AD.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
			AD.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
			CASE WHEN AD.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
			AD.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
			AD.ASP_UnderElevenComments	AS UnderElevenComment,
			ASP_LeaveStartTimeSec	AS LeaveStartTime,
			ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
			ASP_CreatedBy AS CreatedBy,
			ASP_CreatedDate AS CreatedDate,
			ASP_UpdatedBy AS UpdatedBy,
			ASP_UpdatedDate AS UpdatedDate,
			CASE WHEN AL.IsHomeTeam IN (0, 2) AND AL.IsAvailable = 0 AND AAP.AAP_AllocationsAPID IS NULL
				 THEN 0 
				 ELSE 1 
				 END AS AddSPExclFilter	
		FROM #TempSPARCH AL
		LEFT JOIN  #TempDutyARCH AD on AL.AL_SchedulingTeamID = AD.AL_SchedulingTeamID
									and AL.ScheduledPersonID = AD.ASP_SchedulingPersonID
									AND AL.dDateTime = ASP_DutyDate
		LEFT JOIN AllocationsAddPersons_ARCH AAP on AL.AL_AllocationsID = AAP.AAP_AllocationsID
											  AND AL.ScheduledPersonID = AAP.AAP_SchedulingPersonID
											  AND AL.ixDayInWeek = AAP.AAP_iDay
		WHERE NOT EXISTS ( SELECT 1
							FROM AllocationsDelPersons_ARCH ADP
							WHERE ADP_AllocationsID = AL.AL_AllocationsID
							AND AL.ScheduledPersonID = ADP.ADP_SchedulingPersonID
						  )
	  ) FD WHERE AddSPExclFilter = 1   

	IF(@ExtractDays != 'ALL')
	BEGIN
		SET @queryCond = @queryCond + ' AND AL.iDay IN (' + @ExtractDays + ')'
	END
	IF(@includeLeave = 1)
	BEGIN
		SET @queryCond = @queryCond + ' AND AL.LeaveId = 0'
	END
	IF(@staffNumber != '')
	BEGIN
	
	   DECLARE @staffNumberValArrStr varchar(max)
	   SET @staffNumberValArrStr = REPLACE(@staffNumberVal, ',', ''',''')
	   SET @queryCond = @queryCond + CASE @staffNumber
						WHEN 0 THEN ' AND SP.UD_StaffNumber Like ''%' + @staffNumberVal + '%'''
						WHEN 1 THEN ' AND SP.UD_StaffNumber Like ''' + @staffNumberVal + '%'''
						WHEN 2 THEN ' AND SP.UD_StaffNumber Like ''%' + @staffNumberVal + ''''
						WHEN 3 THEN ' AND SP.UD_StaffNumber = ''' + @staffNumberVal + ''''
						WHEN 4 THEN ' AND SP.UD_StaffNumber != ''' + @staffNumberVal + ''''
						WHEN 5 THEN ' AND SP.UD_StaffNumber in(' + '''' + @staffNumberValArrStr + '''' + ')'
					END
	END

	IF((@extractJob >= 0) AND (isNull(@extractJobVal, '') != ''))
	BEGIN

	    DECLARE @extractJobValArrStr varchar(max)
	    SET @extractJobValArrStr = REPLACE(@extractJobVal, ',', ''',''')
		SET @queryCond = @queryCond + CASE @extractJob
						WHEN 0 THEN ' AND MJ.JobName Like ''%' + @extractJobVal + '%'''
						WHEN 1 THEN ' AND MJ.JobName Like ''' + @extractJobVal + '%'''
						WHEN 2 THEN ' AND MJ.JobName Like ''%' + @extractJobVal + ''''
						WHEN 3 THEN ' AND MJ.JobName = ''' + @extractJobVal + ''''
						WHEN 4 THEN ' AND MJ.JobName != ''' + @extractJobVal + ''''
						WHEN 5 THEN ' AND MJ.JobName in(' + '''' + @extractJobValArrStr + '''' + ')'
					END
	END

	IF((@extractProgramme >= 0) AND (isNull(@extractProgrammeVal, '') != ''))
	BEGIN
	   DECLARE @extractProgrammeValArrStr varchar(max)
	   SET @extractProgrammeValArrStr = REPLACE(@extractProgrammeVal, ',', ''',''')
	   SET @queryCond = @queryCond + CASE @extractProgramme
						WHEN 0 THEN ' AND ( PG.Programme Like ''%' + @extractProgrammeVal + '%'''
						              +' OR PG2.Programme Like ''%' + @extractProgrammeVal + '%'''
									  +' OR PG3.Programme Like ''%' + @extractProgrammeVal + '%'''
									  +' OR PG4.Programme Like ''%' + @extractProgrammeVal + '%'''
									  +' OR PG5.Programme Like ''%' + @extractProgrammeVal + '%'''
									  +' OR PG6.Programme Like ''%' + @extractProgrammeVal + '%'')'
						WHEN 1 THEN ' AND ( PG.Programme Like ''' + @extractProgrammeVal + '%'''
						              +' OR PG2.Programme Like ''' + @extractProgrammeVal + '%'''
									  +' OR PG3.Programme Like ''' + @extractProgrammeVal + '%'''
									  +' OR PG4.Programme Like ''' + @extractProgrammeVal + '%'''
									  +' OR PG5.Programme Like ''' + @extractProgrammeVal + '%'''
									  +' OR PG6.Programme Like ''' + @extractProgrammeVal + '%'')'
						WHEN 2 THEN ' AND ( PG.Programme Like ''%' + @extractProgrammeVal + ''''
						              +' OR PG2.Programme Like ''%' + @extractProgrammeVal + ''''
									  +' OR PG3.Programme Like ''%' + @extractProgrammeVal + ''''
									  +' OR PG4.Programme Like ''%' + @extractProgrammeVal + ''''
									  +' OR PG5.Programme Like ''%' + @extractProgrammeVal + ''''
									  +' OR PG6.Programme Like ''%' + @extractProgrammeVal + ''')'
						WHEN 3 THEN ' AND ( PG.Programme = ''' + @extractProgrammeVal + ''''
						              +' OR PG2.Programme = ''' + @extractProgrammeVal + ''''
									  +' OR PG3.Programme = ''' + @extractProgrammeVal + ''''
									  +' OR PG4.Programme = ''' + @extractProgrammeVal + ''''
									  +' OR PG5.Programme = ''' + @extractProgrammeVal + ''''
									  +' OR PG6.Programme = ''' + @extractProgrammeVal + ''')'
						WHEN 4 THEN ' AND ( PG.Programme != ''' + @extractProgrammeVal + ''''
						              +' OR PG2.Programme != ''' + @extractProgrammeVal + ''''
									  +' OR PG3.Programme != ''' + @extractProgrammeVal + ''''
									  +' OR PG4.Programme != ''' + @extractProgrammeVal + ''''
									  +' OR PG5.Programme != ''' + @extractProgrammeVal + ''''
									  +' OR PG6.Programme != ''' + @extractProgrammeVal + ''')'
						WHEN 5 THEN ' AND ( PG.Programme in(' + '''' + @extractProgrammeValArrStr + '''' + ')'
						              +' OR  PG2.Programme in(' + '''' + @extractProgrammeValArrStr + '''' + ')'
									  +' OR  PG3.Programme in(' + '''' + @extractProgrammeValArrStr + '''' + ')'
									  +' OR  PG4.Programme in(' + '''' + @extractProgrammeValArrStr + '''' + ')'
									  +' OR  PG5.Programme in(' + '''' + @extractProgrammeValArrStr + '''' + ')'
									  +' OR  PG6.Programme in(' + '''' + @extractProgrammeValArrStr + '''' + ') )' 

					END
	END

	IF((@extractJobProgramme >= 0) AND (isNull(@extractJobProgrammeVal, '') != ''))
	BEGIN
	   DECLARE @extractJobProgrammeValArrStr varchar(max)
	   SET @extractJobProgrammeValArrStr = REPLACE(@extractJobProgrammeVal, ',', ''',''')
	   SET @queryCond = @queryCond + CASE @extractJobProgramme
						WHEN 0 THEN ' AND JPG.Programme Like ''%' + @extractJobProgrammeVal + '%'''
						WHEN 1 THEN ' AND JPG.Programme Like ''' + @extractJobProgrammeVal + '%'''
						WHEN 2 THEN ' AND JPG.Programme Like ''%' + @extractJobProgrammeVal + ''''
						WHEN 3 THEN ' AND JPG.Programme = ''' + @extractJobProgrammeVal + ''''
						WHEN 4 THEN ' AND JPG.Programme != ''' + @extractJobProgrammeVal + ''''
						WHEN 5 THEN ' AND JPG.Programme in(' + '''' + @extractJobProgrammeValArrStr + '''' + ')'
					END
	END

	IF((@extractContact >= 0) AND (isNull(@extractContactVal, '') != ''))
	BEGIN
	DECLARE @extractContactValArrStr varchar(max)
	SET @extractContactValArrStr = REPLACE(@extractContactVal, ',', ''',''')
		SET @queryCond = @queryCond + CASE @extractContact
						WHEN 0 THEN ' AND MJ.Contact Like ''%' + @extractContactVal + '%'''
						WHEN 1 THEN ' AND MJ.Contact Like ''' + @extractContactVal + '%'''
						WHEN 2 THEN ' AND MJ.Contact Like ''%' + @extractContactVal + ''''
						WHEN 3 THEN ' AND MJ.Contact = ''' + @extractContactVal + ''''
						WHEN 4 THEN ' AND MJ.Contact != ''' + @extractContactVal + ''''
						WHEN 5 THEN ' AND MJ.Contact in(' + '''' + @extractContactValArrStr + '''' + ')'
					END
	END

	IF((@extractLocation >= 0) AND (isNull(@extractLocationVal, '') != ''))
	BEGIN
	   DECLARE @extractLocationValArrStr varchar(max)
	   SET @extractLocationValArrStr = REPLACE(@extractLocationVal, ',', ''',''')
	   SET @queryCond = @queryCond + CASE @extractLocation
						WHEN 0 THEN ' AND MJ.Location Like ''%' + @extractLocationVal + '%'''
						WHEN 1 THEN ' AND MJ.Location Like ''' + @extractLocationVal + '%'''
						WHEN 2 THEN ' AND MJ.Location Like ''%' + @extractLocationVal + ''''
						WHEN 3 THEN ' AND MJ.Location = ''' + @extractLocationVal + ''''
						WHEN 4 THEN ' AND MJ.Location != ''' + @extractLocationVal + ''''
						WHEN 5 THEN ' AND MJ.Location in(' + '''' + @extractLocationValArrStr + '''' + ')'
					END
	END

	IF((@extractDuty >= 0) AND (isNull(@extractDutyVal, '') != ''))
	BEGIN
	   DECLARE @extractDutyValArrStr varchar(max)
	   SET @extractDutyValArrStr = REPLACE(@extractDutyVal, ',', ''',''')
	   SET @queryCond = @queryCond + CASE @extractDuty
						WHEN 0 THEN ' AND AL.DutyName Like ''%' + @extractDutyVal + '%'''
						WHEN 1 THEN ' AND AL.DutyName Like ''' + @extractDutyVal + '%'''
						WHEN 2 THEN ' AND AL.DutyName Like ''%' + @extractDutyVal + ''''
						WHEN 3 THEN ' AND AL.DutyName = ''' + @extractDutyVal + ''''
						WHEN 4 THEN ' AND AL.DutyName != ''' + @extractDutyVal + ''''
						WHEN 5 THEN ' AND AL.DutyName in(' + '''' + @extractDutyValArrStr + '''' + ')'
					END
	END

	IF(@extractFilterBy > 0)
	BEGIN
		SET @queryCond = @queryCond + CASE @extractFilterByCond
						WHEN 0 THEN ' AND Al.Duration < ' + convert(varchar, (@extractFilterByCondVal * 3600))
						WHEN 1 THEN ' AND Al.Duration <=' + convert(varchar, (@extractFilterByCondVal * 3600))
						WHEN 2 THEN ' AND Al.Duration = ' + convert(varchar, (@extractFilterByCondVal * 3600))
						WHEN 3 THEN ' AND Al.Duration > ' + convert(varchar, (@extractFilterByCondVal * 3600))
						WHEN 4 THEN ' AND Al.Duration >=' + convert(varchar, (@extractFilterByCondVal * 3600))
						WHEN 5 THEN ' AND Al.Duration <>' + convert(varchar, (@extractFilterByCondVal * 3600))
					END
	END

	IF(@extractGroup1 > 0)
	BEGIN
		SET @grpByColName = CASE @extractGroup1
						WHEN 1 THEN ' SP.UD_DisplayName'
						WHEN 2 THEN ' SP.UD_StaffNumber'
						WHEN 3 THEN ' isNull(AL.sortcode, SPTL.sortcode)'
						WHEN 4 THEN ' AL.DutyDate'
						WHEN 5 THEN ' AL.WeekNumber'
						WHEN 6 THEN ' AL.iDay'
						WHEN 7 THEN ' AL.DutyName'
					END

		SET @grpByColNameList = CASE @extractGroup1
						WHEN 1 THEN ' SP.UD_DisplayName DisplayName'
						WHEN 2 THEN ' SP.UD_StaffNumber StaffNumber'
						WHEN 3 THEN ' isNull(AL.sortcode, SPTL.sortcode)'
						WHEN 4 THEN ' AL.DutyDate'
						WHEN 5 THEN ' AL.WeekNumber'
						WHEN 6 THEN ' AL.iDay'
						WHEN 7 THEN ' AL.DutyName'
					END
	END

	IF(@extractGroup2 > 0)
	BEGIN
		SET @grpByColName = @grpByColName + CASE @extractGroup2
						WHEN 1 THEN ', SP.UD_DisplayName'
						WHEN 2 THEN ', SP.UD_StaffNumber'
						WHEN 3 THEN ', isNull(AL.sortcode, SPTL.sortcode)'
						WHEN 4 THEN ', AL.DutyDate'
						WHEN 5 THEN ', AL.WeekNumber'
						WHEN 6 THEN ', AL.iDay'
						WHEN 7 THEN ', AL.DutyName'
					END

		SET @grpByColNameList = @grpByColNameList + CASE @extractGroup2
						WHEN 1 THEN ', SP.UD_DisplayName DisplayName'
						WHEN 2 THEN ', SP.UD_StaffNumber StaffNumber'
						WHEN 3 THEN ', isNull(AL.sortcode, SPTL.sortcode)'
						WHEN 4 THEN ', AL.DutyDate'
						WHEN 5 THEN ', AL.WeekNumber'
						WHEN 6 THEN ', AL.iDay'
						WHEN 7 THEN ', AL.DutyName'
					END

	END

	IF(@extractFilter > 0)
	BEGIN
		SELECT @dutyFilterStr = DutyFilter, 
		       @sortCodeFilter = SortCodeFilter, 
			   @sortOrder = SortOrder, 
			   @andMatch = AndMatch, 
			   @staffName = StaffName,		
			   @costCode = CostCode, 
			   @skillName = SkillName, 
			   @dutyLable = DutyLabel,
			   @dutyLable2 = DutyLabel,
			   @dutyLable3 = DutyLabel,
			   @dutyLable4 = DutyLabel,
			   @dutyLable5 = DutyLabel,
			   @dutyLable6 = DutyLabel
		  FROM AutoPagesFilters 
		 where id = @extractFilter AND isPublic = 1

		
		IF(@andMatch = 0)
		  BEGIN
			IF((@dutyFilterStr is not null) AND (LEN(@dutyFilterStr) > 1))
			BEGIN 
				IF(right(@dutyFilterStr, 1) = '*')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName in(' + '''' + REPLACE(@dutyFilterStr, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyFilterStr, 1) = '&')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName = ' + '''' + REPLACE(@dutyFilterStr, '&', ''' AND AL.DutyName = ''') + '''' + ')'
				END
				IF(right(@dutyFilterStr, 1) = ';')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName = ' + '''' + REPLACE(@dutyFilterStr, ';', ''' OR AL.DutyName = ''') + '''' + ')'
				END
				IF(right(@dutyFilterStr, 1) = '!')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' OR (AL.DutyName NOT in(' + '''' + REPLACE(@dutyFilterStr, '!', ''',''') + '''' + '))'
				END
			END
			---2nd
			IF((@sortCodeFilter is not null) AND (LEN(@sortCodeFilter) > 1))
			BEGIN 
				IF(right(@sortCodeFilter, 1) = '*')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (isNull(SPTL.sortcode, 0) in(' + '''' + REPLACE(@sortCodeFilter, '*', ''',''') + '''' + '))'
				END
				IF(right(@sortCodeFilter, 1) = '&')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (SPTL.sortcode = ' + '''' + REPLACE(@sortCodeFilter, '&', ''' AND SPTL.sortcode = ''') + '''' + ')'
				END
				IF(right(@sortCodeFilter, 1) = ';')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (SPTL.sortcode = ' + '''' + REPLACE(@sortCodeFilter, ';', ''' OR SPTL.sortcode = ''') + '''' + ')'
				END
				IF(right(@dutyFilterStr, 1) = '!')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' OR (isNull(SPTL.sortcode, 0) NOT in(' + '''' + REPLACE(@sortCodeFilter, '!', ''',''') + '''' + '))'
				END
			END
			--3rd
			IF((@costCode is not null) AND (LEN(@costCode) > 1))
			BEGIN 
				IF(right(@costCode, 1) = '*')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName in(' + '''' + REPLACE(@costCode, '*', ''',''') + '''' + '))'
				END
				IF(right(@costCode, 1) = '&')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName = ' + '''' + REPLACE(@costCode, '&', ''' AND CD.ChargeWbsCodeName = ''') + '''' + ')'
				END
				IF(right(@costCode, 1) = ';')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName = ' + '''' + REPLACE(@costCode, ';', ''' OR CD.ChargeWbsCodeName = ''') + '''' + ')'
				END
				IF(right(@costCode, 1) = '!')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' OR )CD.ChargeWbsCodeName NOT in(' + '''' + REPLACE(@costCode, '!', ''',''') + '''' + '))'
				END
			END
			--4th
			IF((@skillName is not null) AND (LEN(@skillName) > 1))
			BEGIN 
				IF(right(@skillName, 1) = '*')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename in(' + '''' + REPLACE(@skillName, '*', ''',''') + '''' + '))'
				END
				IF(right(@skillName, 1) = '&')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename = ' + '''' + REPLACE(@skillName, '&', ''' AND SKP.programmename = ''') + '''' + ')'
				END
				IF(right(@costCode, 1) = ';')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename = ' + '''' + REPLACE(@skillName, ';', ''' OR SKP.programmename = ''') + '''' + ')'
				END
				IF(right(@skillName, 1) = '!')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' OR (SKP.programmename NOT in(' + '''' + REPLACE(@skillName, '!', ''',''') + '''' + '))'
				END
			END
			--5th
			IF((@dutyLable is not null) AND (LEN(@dutyLable) > 1))
			BEGIN 
				IF(right(@dutyLable, 1) = '*')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID in(' + '''' + REPLACE(@dutyLable, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable, 1) = '&')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID = ' + '''' + REPLACE(@dutyLable, '&', ''' AND PG.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable, 1) = ';')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID = ' + '''' + REPLACE(@dutyLable, ';', ''' OR PG.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable, 1) = '!')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' OR (PG.ID NOT in(' + '''' + REPLACE(@dutyLable, '!', ''',''') + '''' + '))'
				END
			END

			IF((@dutyLable2 is not null) AND (LEN(@dutyLable2) > 1))
			BEGIN 
				IF(right(@dutyLable2, 1) = '*')
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID ='+''''+@dutyLable2+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable2) > 1)
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID in(' + '''' + REPLACE(@dutyLable2, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable2, 1) = '&')
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID ='+''''+@dutyLable2+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable2) > 1)
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID = ' + '''' + REPLACE(@dutyLable2, '&', ''' AND PG2.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable2, 1) = ';')
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID ='+''''+@dutyLable2+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable2) > 1)
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID = ' + '''' + REPLACE(@dutyLable2, ';', ''' OR PG2.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable2, 1) = '!')
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID ='+''''+@dutyLable2+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable2 = left(@dutyLable2, len(@dutyLable2)-1)
					SET @queryCond = @queryCond + ' OR (PG2.ID NOT in(' + '''' + REPLACE(@dutyLable2, '!', ''',''') + '''' + '))'
				END
			END

			IF((@dutyLable3 is not null) AND (LEN(@dutyLable3) > 1))
			BEGIN 
				IF(right(@dutyLable3, 1) = '*')
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID ='+''''+@dutyLable3+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable3) > 1)
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID in(' + '''' + REPLACE(@dutyLable3, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable3, 1) = '&')
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID ='+''''+@dutyLable3+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable3) > 1)
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID = ' + '''' + REPLACE(@dutyLable3, '&', ''' AND PG3.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable3, 1) = ';')
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID ='+''''+@dutyLable3+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable3) > 1)
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID = ' + '''' + REPLACE(@dutyLable3, ';', ''' OR PG3.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable3, 1) = '!')
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID ='+''''+@dutyLable3+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable3 = left(@dutyLable3, len(@dutyLable3)-1)
					SET @queryCond = @queryCond + ' OR (PG3.ID NOT in(' + '''' + REPLACE(@dutyLable3, '!', ''',''') + '''' + '))'
				END
			END

			IF((@dutyLable4 is not null) AND (LEN(@dutyLable4) > 1))
			BEGIN 
				IF(right(@dutyLable4, 1) = '*')
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID ='+''''+@dutyLable4+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable4) > 1)
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID in(' + '''' + REPLACE(@dutyLable4, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable4, 1) = '&')
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID ='+''''+@dutyLable4+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable4) > 1)
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID = ' + '''' + REPLACE(@dutyLable4, '&', ''' AND PG4.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable4, 1) = ';')
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID ='+''''+@dutyLable4+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable4) > 1)
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID = ' + '''' + REPLACE(@dutyLable4, ';', ''' OR PG4.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable4, 1) = '!')
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID ='+''''+@dutyLable4+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable4 = left(@dutyLable4, len(@dutyLable4)-1)
					SET @queryCond = @queryCond + ' OR (PG4.ID NOT in(' + '''' + REPLACE(@dutyLable4, '!', ''',''') + '''' + '))'
				END
			END

			IF((@dutyLable5 is not null) AND (LEN(@dutyLable5) > 1))
			BEGIN 
				IF(right(@dutyLable5, 1) = '*')
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID ='+''''+@dutyLable5+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable5) > 1)
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID in(' + '''' + REPLACE(@dutyLable5, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable5, 1) = '&')
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID ='+''''+@dutyLable5+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable5) > 1)
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID = ' + '''' + REPLACE(@dutyLable5, '&', ''' AND PG5.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable5, 1) = ';')
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID ='+''''+@dutyLable5+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable5) > 1)
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID = ' + '''' + REPLACE(@dutyLable5, ';', ''' OR PG5.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable5, 1) = '!')
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID ='+''''+@dutyLable5+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable5 = left(@dutyLable5, len(@dutyLable5)-1)
					SET @queryCond = @queryCond + ' OR (PG5.ID NOT in(' + '''' + REPLACE(@dutyLable5, '!', ''',''') + '''' + '))'
				END
			END

			IF((@dutyLable6 is not null) AND (LEN(@dutyLable6) > 1))
			BEGIN 
				IF(right(@dutyLable6, 1) = '*')
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID ='+''''+@dutyLable6+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable6) > 1)
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID in(' + '''' + REPLACE(@dutyLable6, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable6, 1) = '&')
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID ='+''''+@dutyLable6+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable6) > 1)
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID = ' + '''' + REPLACE(@dutyLable6, '&', ''' AND PG6.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable6, 1) = ';')
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID ='+''''+@dutyLable6+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable6) > 1)
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID = ' + '''' + REPLACE(@dutyLable6, ';', ''' OR PG6.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable6, 1) = '!')
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID ='+''''+@dutyLable6+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable6 = left(@dutyLable6, len(@dutyLable6)-1)
					SET @queryCond = @queryCond + ' OR (PG6.ID NOT in(' + '''' + REPLACE(@dutyLable6, '!', ''',''') + '''' + '))'
				END
			END

			--6th
			IF((@staffName is not null) AND (LEN(@staffName) > 1))
			BEGIN 
				IF(right(@staffName, 1) = '*')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' OR (SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName in(' + '''' + REPLACE(@staffName, '*', ''',''') + '''' + ') )'
				END
				IF(right(@staffName, 1) = '&')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName = ' + '''' + REPLACE(@staffName, '&', ''' AND SP.UD_DisplayName = ''') + '''' + ')'
				END
				IF(right(@staffName, 1) = ';')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName = ' + '''' + REPLACE(@staffName, ';', ''' OR SP.UD_DisplayName = ''') + '''' + ')'
				END
				IF(right(@staffName, 1) = '!')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' OR ( SP.UD_DisplayName NOT in(' + '''' + REPLACE(@staffName, '!', ''',''') + '''' + ') )'
				END
			END
		END
		ELSE
		BEGIN
			IF((@dutyFilterStr is not null) AND (LEN(@dutyFilterStr) > 1))
			BEGIN 
				IF(right(@dutyFilterStr, 1) = '*')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName in(' + '''' + REPLACE(@dutyFilterStr, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyFilterStr, 1) = '&')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName = ' + '''' + REPLACE(@dutyFilterStr, '&', ''' AND AL.DutyName = ''') + '''' + ')'
				END
				IF(right(@dutyFilterStr, 1) = ';')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName = ' + '''' + REPLACE(@dutyFilterStr, ';', ''' OR AL.DutyName = ''') + '''' + ')'
				END
				IF(right(@dutyFilterStr, 1) = '!')
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName ='+''''+@dutyFilterStr+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @dutyFilterStr) > 1)
				BEGIN
					SET @dutyFilterStr = left(@dutyFilterStr, len(@dutyFilterStr)-1)
					SET @queryCond = @queryCond + ' AND (AL.DutyName NOT in(' + '''' + REPLACE(@dutyFilterStr, '!', ''',''') + '''' + '))'
				END
			END
			---2nd
			IF((@sortCodeFilter is not null) AND (LEN(@sortCodeFilter) > 1))
			BEGIN 
				IF(right(@sortCodeFilter, 1) = '*')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (isNull(SPTL.sortcode, 0) in(' + '''' + REPLACE(@sortCodeFilter, '*', ''',''') + '''' + '))'
				END
				IF(right(@sortCodeFilter, 1) = '&')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (SPTL.sortcode = ' + '''' + REPLACE(@sortCodeFilter, '&', ''' AND SPTL.sortcode = ''') + '''' + ')'
				END
				IF(right(@sortCodeFilter, 1) = ';')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (SPTL.sortcode = ' + '''' + REPLACE(@sortCodeFilter, ';', ''' OR SPTL.sortcode = ''') + '''' + ')'
				END
				IF(right(@dutyFilterStr, 1) = '!')
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (SPTL.sortcode ='+''''+@sortCodeFilter+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @sortCodeFilter) > 1)
				BEGIN
					SET @sortCodeFilter = left(@sortCodeFilter, len(@sortCodeFilter)-1)
					SET @queryCond = @queryCond + ' AND (isNull(SPTL.sortcode, 0) NOT in(' + '''' + REPLACE(@sortCodeFilter, '!', ''',''') + '''' + '))'
				END
			END
			--3rd
			IF((@costCode is not null) AND (LEN(@costCode) > 1))
			BEGIN 
				IF(right(@costCode, 1) = '*')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName in(' + '''' + REPLACE(@costCode, '*', ''',''') + '''' + '))'
				END
				IF(right(@costCode, 1) = '&')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName = ' + '''' + REPLACE(@costCode, '&', ''' AND CD.ChargeWbsCodeName = ''') + '''' + ')'
				END
				IF(right(@costCode, 1) = ';')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName = ' + '''' + REPLACE(@costCode, ';', ''' OR CD.ChargeWbsCodeName = ''') + '''' + ')'
				END
				IF(right(@costCode, 1) = '!')
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName ='+''''+@costCode+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @costCode) > 1)
				BEGIN
					SET @costCode = left(@costCode, len(@costCode)-1)
					SET @queryCond = @queryCond + ' AND (CD.ChargeWbsCodeName NOT in(' + '''' + REPLACE(@costCode, '!', ''',''') + '''' + '))'
				END
			END
			--4th
			IF((@skillName is not null) AND (LEN(@skillName) > 1))
			BEGIN 
				IF(right(@skillName, 1) = '*')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename in(' + '''' + REPLACE(@skillName, '*', ''',''') + '''' + '))'
				END
				IF(right(@skillName, 1) = '&')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename = ' + '''' + REPLACE(@skillName, '&', ''' AND SKP.programmename = ''') + '''' + ')'
				END
				IF(right(@costCode, 1) = ';')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename = ' + '''' + REPLACE(@skillName, ';', ''' OR SKP.programmename = ''') + '''' + ')'
				END
				IF(right(@skillName, 1) = '!')
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename ='+''''+@skillName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @skillName = left(@skillName, len(@skillName)-1)
					SET @queryCond = @queryCond + ' AND (SKP.programmename NOT in(' + '''' + REPLACE(@skillName, '!', ''',''') + '''' + '))'
				END
			END
			--5th
			IF((@dutyLable is not null) AND (LEN(@dutyLable) > 1))
			BEGIN 
				IF(right(@dutyLable, 1) = '*')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @dutyLable) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID in(' + '''' + REPLACE(@dutyLable, '*', ''',''') + '''' + '))'
				END
				IF(right(@dutyLable, 1) = '&')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @dutyLable) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID = ' + '''' + REPLACE(@dutyLable, '&', ''' AND PG.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable, 1) = ';')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @dutyLable) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID = ' + '''' + REPLACE(@dutyLable, ';', ''' OR PG.ID = ''') + '''' + ')'
				END
				IF(right(@dutyLable, 1) = '!')
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID ='+''''+@dutyLable+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @skillName) > 1)
				BEGIN
					SET @dutyLable = left(@dutyLable, len(@dutyLable)-1)
					SET @queryCond = @queryCond + ' AND (PG.ID NOT in(' + '''' + REPLACE(@dutyLable, '!', ''',''') + '''' + '))'
				END
			END
			--6th
			IF((@staffName is not null) AND (LEN(@staffName) > 1))
			BEGIN 
				IF(right(@staffName, 1) = '*')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' AND (SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('*', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName in(' + '''' + REPLACE(@staffName, '*', ''',''') + '''' + ') )'
				END
				IF(right(@staffName, 1) = '&')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('&', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName = ' + '''' + REPLACE(@staffName, '&', ''' AND SP.UD_DisplayName = ''') + '''' + ')'
				END
				IF(right(@staffName, 1) = ';')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX (';', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName = ' + '''' + REPLACE(@staffName, ';', ''' OR SP.UD_DisplayName = ''') + '''' + ')'
				END
				IF(right(@staffName, 1) = '!')
				BEGIN
					SET @staffName = left(@staffName, len(@staffName)-1)
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName ='+''''+@staffName+'''' + ')'
				END
				ELSE IF(CHARINDEX ('!', @staffName) > 1)
				BEGIN
					SET @queryCond = @queryCond + ' AND ( SP.UD_DisplayName NOT in(' + '''' + REPLACE(@staffName, '!', ''',''') + '''' + ') )'
				END
			END
		END
		IF((@extractFilter != '') AND (@sortOrder = 0))
		BEGIN
			SET @orderByStr = @orderByStr + ' ORDER BY SP.UD_DisplayName ASC'
		END
		IF((@extractFilter != '') AND (@sortOrder = 1))
		BEGIN
			SET @orderByStr = @orderByStr + ' ORDER BY SPTL.sortcode ASC'
		END
		IF(@extractFilter = '')
		BEGIN
			SET @orderByStr = @orderByStr + ' ORDER BY ' + @sortingCol + ' ' + @sortingType
		END
	END
	
	IF(@schedulingTeam > 0)
	BEGIN
		SET @queryCond = @queryCond + ' AND SPTL.TeamId = ' + @schedulingTeam
	END
	ELSE
	BEGIN
	   SET @queryCond = @queryCond + ' AND EXISTS  ( 
	   select 1
	   	FROM schedulingTeams st (nolock)
		where 
		  EXISTS (
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				WHERE UR.UR_SchedulingTeamID = st.SchedulingTeamId
				AND UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND UR.UR_UserID = '+ cast(@userID as varchar) +' 
				AND RR.RoleName IN (''Advanced Reports'', ''Scheduling Team Admin'')
			UNION
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				INNER join schedulingTeams stf2 on stf2.divisionId = UR.UR_DivisionId
				WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND RR.RoleName = ''Area Admin''
				AND stf2.schedulingTeamId = st.schedulingTeamId
				AND UR.UR_UserID = '+ cast(@userID as varchar) +' 
			UNION
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND RR.RoleName = ''System Admin''
				AND UR.UR_UserID = '+ cast(@userID as varchar) +' 
			)
			AND SPTL.TeamId = st.SchedulingTeamId
			)'
	END

	IF(@reportType = 0)
	BEGIN
		IF(@extractHistoryData = 1)
		BEGIN
			SET @query = '
			SELECT distinct ' +@defaultColsList + ',
			   STUFF((SELECT '','' + History 
										from History HIS (nolock)
										where his.AttributeID = AL.AllocationDutyID AND his.HistoryType = 8
										 order by AL.AllocationDutyID
									FOR XML PATH(''''), TYPE
									).value(''.'', ''NVARCHAR(MAX)''),1,1,'''')   AS  History
			from #TempAllocations AL (NOLOCK)
			INNER join Timedimension TD (NOLOCK) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek		  
			INNER JOIN Userdetails SP (NOLOCK) ON SP.UD_UserID = AL.SchedulingPersonID
			INNER JOIN ScheduledPersonTeam_LINK SPTL (NOLOCK) ON SPTL.ScheduledPersonID = AL.SchedulingPersonID  AND SPTL.TeamID=AL.SchedulingTeamId
			INNER JOIN ScheduledPersonTeam_LINK STL (NOLOCK) ON STL.ScheduledPersonID = AL.SchedulingPersonID 
			INNER JOIN schedulingTeams ST (NOLOCK) on ST.schedulingTeamId = STL.TeamID
			LEFT JOIN vAllocations_jobs MJ (NOLOCK) ON MJ.AllocationDutyID = AL.AllocationDutyID AND 1 = CASE When ' + CAST (@includeJobData AS varchar) + ' = 1 then 0 else 1 end 
			Left JOIN Programmes PG (NOLOCK) ON PG.ID = AL.dutyProgramId
			Left JOIN Programmes PG2 (NOLOCK) ON PG2.ID = AL.dutyProgramId2
			Left JOIN Programmes PG3 (NOLOCK) ON PG3.ID = AL.dutyProgramId3
			Left JOIN Programmes PG4 (NOLOCK) ON PG4.ID = AL.dutyProgramId4
			Left JOIN Programmes PG5 (NOLOCK) ON PG5.ID = AL.dutyProgramId5
			Left JOIN Programmes PG6 (NOLOCK) ON PG6.ID = AL.dutyProgramId6
			left join Programmes JPG (NOLOCK) ON JPG.ID = MJ.ProgrammeId
			LEFT JOIN ChargingDutyMapping_Link CDML (NOLOCK) ON CDML.AllocationId = AL.AllocationSPID
			LEFT JOIN ChargeWbsCode CD (NOLOCK) ON CD.ChargeWbsCodeId = CDML.ChargeCodeId
			LEFT JOIN skills_programmes SKP (NOLOCK) ON SKP.TeamID = AL.SchedulingTeamId
			LEFT JOIN LeaveApplications LA (NOLOCK) on LA.dDate = TD.dDateTime and LA.SchedulingPersonID = AL.SchedulingPersonID
			LEFT JOIN ref_LeaveApplications_Amounts LAA (NOLOCK) on LAA.ApplicationID = LA.ID 
			LEFT JOIN LeaveAllocateTypes LT (NOLOCK) on LT.id = LAA.LeaveTypeID
			where SPTL.scheduledType = 1
			AND TD.ixYearWeek between ' + @weekFrom +' AND ' + @weekTo +'
			AND AL.DutyDate between STL.startdate AND STL.enddate
			AND STL.scheduledType = 1
			AND STL.IsHomeTeam = 1
			AND AL.DutyDate between SPTL.startdate and  SPTL.enddate '
			+ @queryCond + @orderByStr
		END
		ELSE
		BEGIN
			SET @query = '
			select distinct ' +@defaultColsList + '
			from #TempAllocations AL (NOLOCK)
			INNER join Timedimension TD (NOLOCK) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek	
			INNER JOIN Userdetails SP (NOLOCK) ON SP.UD_UserID = AL.SchedulingPersonID
			INNER JOIN ScheduledPersonTeam_LINK SPTL (NOLOCK) ON SPTL.ScheduledPersonID = AL.SchedulingPersonID  AND SPTL.TeamID=AL.SchedulingTeamId
			INNER JOIN ScheduledPersonTeam_LINK STL (NOLOCK) ON STL.ScheduledPersonID = AL.SchedulingPersonID 
			INNER JOIN schedulingTeams ST (NOLOCK) on ST.schedulingTeamId = STL.TeamID
			LEFT JOIN vAllocations_jobs MJ (NOLOCK) ON MJ.AllocationDutyID = AL.AllocationDutyID AND 1 = CASE When ' + CAST (@includeJobData AS varchar) + ' = 1 then 0 else 1 end
			Left JOIN Programmes PG (NOLOCK) ON PG.ID = AL.dutyProgramId 
			Left JOIN Programmes PG2 (NOLOCK) ON PG2.ID = AL.dutyProgramId2
			Left JOIN Programmes PG3 (NOLOCK) ON PG3.ID = AL.dutyProgramId3
			Left JOIN Programmes PG4 (NOLOCK) ON PG4.ID = AL.dutyProgramId4
			Left JOIN Programmes PG5 (NOLOCK) ON PG5.ID = AL.dutyProgramId5
			Left JOIN Programmes PG6 (NOLOCK) ON PG6.ID = AL.dutyProgramId6
			left join Programmes JPG (NOLOCK) ON JPG.ID = MJ.ProgrammeId
			LEFT JOIN ChargingDutyMapping_Link CDML (NOLOCK) ON CDML.AllocationId = AL.AllocationSPID
			LEFT JOIN ChargeWbsCode CD (NOLOCK) ON CD.ChargeWbsCodeId = CDML.ChargeCodeId
			LEFT JOIN skills_programmes SKP (NOLOCK) ON SKP.TeamID = AL.SchedulingTeamId
			LEFT JOIN LeaveApplications LA (NOLOCK) ON LA.dDate = TD.dDateTime and LA.SchedulingPersonID = AL.SchedulingPersonID
			LEFT JOIN ref_LeaveApplications_Amounts (NOLOCK) LAA on LAA.ApplicationID = LA.ID 
			LEFT JOIN LeaveAllocateTypes LT (NOLOCK) on LT.id = LAA.LeaveTypeID
			where SPTL.scheduledType = 1
			AND TD.ixYearWeek between ' + @weekFrom +' AND ' + @weekTo +'
			AND AL.DutyDate between STL.startdate AND STL.enddate
			AND STL.scheduledType = 1
			AND STL.IsHomeTeam = 1
			AND AL.DutyDate between SPTL.startdate and SPTL.enddate '
			+ @queryCond + @orderByStr
		END
	END
	ELSE
	BEGIN
		SET @query = '
			select distinct ' +@grpByColNameList + ', SUM(Al.Duration) Duration
			from #TempAllocations AL (NOLOCK)
			INNER join Timedimension TD (NOLOCK) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek	
			INNER JOIN Userdetails SP (NOLOCK) ON SP.UD_UserID = AL.SchedulingPersonID
			INNER JOIN ScheduledPersonTeam_LINK SPTL (NOLOCK) ON SPTL.ScheduledPersonID = AL.SchedulingPersonID AND SPTL.TeamID=AL.SchedulingTeamId
			LEFT JOIN vAllocations_jobs MJ (NOLOCK) ON MJ.AllocationDutyID = AL.AllocationDutyID AND 1 = CASE When ' + CAST (@includeJobData AS varchar) + ' = 1 then 0 else 1 end  
			Left JOIN Programmes PG (NOLOCK) ON PG.ID = AL.dutyProgramId
			Left JOIN Programmes PG2 (NOLOCK) ON PG2.ID = AL.dutyProgramId2
			Left JOIN Programmes PG3 (NOLOCK) ON PG3.ID = AL.dutyProgramId3
			Left JOIN Programmes PG4 (NOLOCK) ON PG4.ID = AL.dutyProgramId4
			Left JOIN Programmes PG5 (NOLOCK) ON PG5.ID = AL.dutyProgramId5
			Left JOIN Programmes PG6 (NOLOCK) ON PG6.ID = AL.dutyProgramId6
			LEFT JOIN ChargingDutyMapping_Link CDML (NOLOCK) ON CDML.AllocationId = AL.AllocationSPID
			LEFT JOIN ChargeWbsCode CD (NOLOCK) ON CD.ChargeWbsCodeId = CDML.ChargeCodeId
			LEFT JOIN skills_programmes SKP (NOLOCK) ON SKP.TeamID = AL.SchedulingTeamId
 		   where SPTL.scheduledType = 1
			 AND TD.ixYearWeek between ' + @weekFrom +' AND ' + @weekTo +'
			 AND AL.DutyDate between SPTL.startdate and SPTL.enddate '
			+ @queryCond 
			+ ' Group BY ' + @grpByColName 
	END
	
	EXEC (@query)
	
END