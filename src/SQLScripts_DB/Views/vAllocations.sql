USE [BBCSchedules]
GO

/****** Object:  View [dbo].[vAllocations]    Script Date: 06/12/2025 18:59:50 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


CREATE OR ALTER            VIEW [dbo].[vAllocations]
AS
 SELECT AL_AllocationsID	AS AllocationID,
		ASP_AllocationsSPID AS AllocationSPID,
		AD_AllocationsDutyID AS AllocationDutyID,
		NULL 				AS StaffNumber ,
		CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				WHEN AD.AD_DutyType IN (8,11)
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
		CASE WHEN AD_DutyType IN (8,11)
			THEN ISNULL(ASP_LeaveDuration,0)
			ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
		AL.AL_WeekNumber AS WeekNumber,
		TD.ixDayInWeek AS iDay,
		ISNULL(AD_StartTimeSec,0) AS StartTime ,
		ISNULL(AD_EndTimeSec,0) AS EndTime,
		NULL	AS ActingGrade,
		ISNULL(ASP_SortCode,SL.SortCode)		AS SortCode ,
		0		AS LeaveID,
		NULL AS ManualERR,
		AD.AD_Comments AS DutyComments,
		ASP.ASP_Comments AS PersonComments,
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
	    SL.ScheduledPersonID	AS SchedulingPersonID,
		TD.dDateTime AS DutyDate,
		AD.AD_DutyStartTimeLocal AS StartDate,
		AD.AD_DutyEndTimeLocal AS EndDate,
		SL.IsHomeTeam AS IsHomeTeam ,
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
		ASP.ASP_OverTwelveStatus as MarkOverTwelve,
		ASP.ASP_OverTwelveHrs AS OverTwelveHrs,
		ASP.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
		ASP.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
		ASP.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
		Asp.ASP_UnderElevenComments	AS UnderElevenComment,
		ASP_LeaveStartTimeSec	AS LeaveStartTime,
		ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
		ASP_CreatedBy AS CreatedBy,
		ASP_CreatedDate AS CreatedDate,
		ASP_UpdatedBy AS UpdatedBy,
		ASP_UpdatedDate AS UpdatedDate
  FROM  Allocations AL WITH(NOLOCK)
  INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
  INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
  LEFT JOIN AllocationsScheduledPersons ASP WITH(NOLOCK) ON AL.AL_AllocationsID=ASP_AllocationsID
														AND TD.ixDayInWeek = ASP.ASP_iDay
														AND SL.ScheduledPersonID = ASP.ASP_SchedulingPersonID
  LEFT JOIN AllocationsDuties AD WITH(NOLOCK) ON AD.AD_AllocationsDutyID = ASP.ASP_AllocationsDutyID
  WHERE SL.scheduledType = 1
    AND SL.IsHomeTeam = 1 
	AND TD.dDateTime BETWEEn SL.StartDate and SL.EndDate
    AND NOT EXISTS ( SELECT 1 FROM ArchivedWeeks AW WITH(NOLOCK) WHERE AW.WeekNumber = AL.AL_WeekNumber) 
UNION ALL
 SELECT AL.AL_AllocationsID	AS AllocationID,
		ASP_AllocationsSPID AS AllocationSPID,
		AD_AllocationsDutyID AS AllocationDutyID,
		NULL 				AS StaffNumber ,
		CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				WHEN AD.AD_DutyType IN (8,11)
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
		CASE WHEN AD_DutyType IN (8,11)
			THEN ISNULL(ASP_LeaveDuration,0)
			ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
		AL.AL_WeekNumber AS WeekNumber,
		TD.ixDayInWeek AS iDay,
		ISNULL(AD_StartTimeSec,0) AS StartTime ,
		ISNULL(AD_EndTimeSec,0) AS EndTime,
		NULL	AS ActingGrade,
		ISNULL(AA.AAP_SortCode,SL.SortCode)			AS SortCode ,
		0		AS LeaveID,
		NULL AS ManualERR,
		AD.AD_Comments AS DutyComments,
		AA.AAP_Comments AS PersonComments,
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
	    SL.ScheduledPersonID	AS SchedulingPersonID,
		TD.dDateTime AS DutyDate,
		AD.AD_DutyStartTimeLocal AS StartDate,
		AD.AD_DutyEndTimeLocal AS EndDate,
		SL.IsHomeTeam AS IsHomeTeam ,
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
		ASP_DutyTeamID AS DutyTeamID ,
		AD.AD_PlannedDuration AS PlannedDuration,  
		ASP.ASP_OverTwelveStatus as MarkOverTwelve,
		ASP.ASP_OverTwelveHrs AS OverTwelveHrs,
		ASP.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
		ASP.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
		ASP.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
		Asp.ASP_UnderElevenComments	AS UnderElevenComment,
		ASP_LeaveStartTimeSec	AS LeaveStartTime,
		ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
		ASP_CreatedBy AS CreatedBy,
		ASP_CreatedDate AS CreatedDate,
		ASP_UpdatedBy AS UpdatedBy,
		ASP_UpdatedDate AS UpdatedDate
	FROM Allocations AS AL (nolock)
    INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
    INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
	LEFT JOIN AllocationsAddPersons AA (nolock) on AL_AllocationsID = AA.AAP_AllocationsID  AND TD.ixDayInWeek = AA.AAP_iDay
										AND SL.ScheduledPersonID = AA.AAP_SchedulingPersonID
	LEFT JOIN AllocationsScheduledPersons ASP (nolock) on AAP_AllocationsSPID = ASP_AllocationsSPID
	LEFT JOIN AllocationsDuties AD (nolock) on AD_AllocationsDutyID = ASP_AllocationsDutyID
	LEFT JOIN Allocations ALA (nolock) ON ALA.AL_AllocationsID = AD_AllocationsID AND ALA.AL_Status <> 9
	WHERE SL.scheduledType = 1
      AND SL.IsHomeTeam = 0
	  AND SL.IsAvailable = 1
	  AND TD.dDateTime BETWEEn SL.StartDate and SL.EndDate
	  AND NOT EXISTS ( SELECT 1 FROM ArchivedWeeks AW WITH(NOLOCK) WHERE AW.WeekNumber = AL.AL_WeekNumber)	  
UNION ALL
 SELECT AL.AL_AllocationsID	AS AllocationID,
		ASP_AllocationsSPID AS AllocationSPID,
		AD_AllocationsDutyID AS AllocationDutyID,
		NULL 				AS StaffNumber ,
		CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				WHEN AD.AD_DutyType IN (8,11)
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
		CASE WHEN AD_DutyType IN (8,11)
			THEN ISNULL(ASP_LeaveDuration,0)
			ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
		AL.AL_WeekNumber AS WeekNumber,
		TD.ixDayInWeek AS iDay,
		ISNULL(AD_StartTimeSec,0) AS StartTime ,
		ISNULL(AD_EndTimeSec,0) AS EndTime,
		NULL	AS ActingGrade,
		ISNULL(AA.AAP_SortCode,SL.SortCode)		AS SortCode ,
		0		AS LeaveID,
		NULL AS ManualERR,
		AD.AD_Comments AS DutyComments,
		AA.AAP_Comments AS PersonComments,
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
	    SL.ScheduledPersonID	AS SchedulingPersonID,
		TD.dDateTime AS DutyDate,
		AD.AD_DutyStartTimeLocal AS StartDate,
		AD.AD_DutyEndTimeLocal AS EndDate,
		SL.IsHomeTeam AS IsHomeTeam ,
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
		ASP_DutyTeamID AS DutyTeamID ,
		AD.AD_PlannedDuration AS PlannedDuration,  
		ASP.ASP_OverTwelveStatus as MarkOverTwelve,
		ASP.ASP_OverTwelveHrs AS OverTwelveHrs,
		ASP.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
		ASP.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
		ASP.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
		Asp.ASP_UnderElevenComments	AS UnderElevenComment,
		ASP_LeaveStartTimeSec	AS LeaveStartTime,
		ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
		ASP_CreatedBy AS CreatedBy,
		ASP_CreatedDate AS CreatedDate,
		ASP_UpdatedBy AS UpdatedBy,
		ASP_UpdatedDate AS UpdatedDate
	FROM Allocations AS AL (nolock)
    INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
    INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
	INNER JOIN AllocationsAddPersons AA  (nolock) on AL_AllocationsID = AA.AAP_AllocationsID  AND TD.ixDayInWeek = AA.AAP_iDay
										AND SL.ScheduledPersonID = AA.AAP_SchedulingPersonID
	LEFT JOIN AllocationsScheduledPersons ASP (nolock) on AAP_AllocationsSPID = ASP_AllocationsSPID
	LEFT JOIN AllocationsDuties AD (nolock) on AD_AllocationsDutyID = ASP_AllocationsDutyID
	LEFT JOIN Allocations ALA (nolock) ON ALA.AL_AllocationsID = AD_AllocationsID AND ALA.AL_Status <> 9
	WHERE SL.scheduledType = 1
      AND SL.IsHomeTeam = 0
	  AND SL.IsAvailable = 0
	  AND TD.dDateTime BETWEEn SL.StartDate and SL.EndDate
	  AND NOT EXISTS ( SELECT 1 FROM ArchivedWeeks AW WITH(NOLOCK) WHERE AW.WeekNumber = AL.AL_WeekNumber)	  
UNION ALL
 SELECT AL_AllocationsID	AS AllocationID,
		ASP_AllocationsSPID AS AllocationSPID,
		AD_AllocationsDutyID AS AllocationDutyID,
		NULL 				AS StaffNumber ,
		CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				WHEN AD.AD_DutyType IN (8,11)
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
		CASE WHEN AD_DutyType IN (8,11)
			THEN ISNULL(ASP_LeaveDuration,0)
			ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
		AL.AL_WeekNumber AS WeekNumber,
		ASP.ASP_iDay AS iDay,
		ISNULL(AD_StartTimeSec,0) AS StartTime ,
		ISNULL(AD_EndTimeSec,0) AS EndTime,
		NULL	AS ActingGrade,
		ASP_SortCode		AS SortCode ,
		0		AS LeaveID,
		NULL AS ManualERR,
		AD.AD_Comments AS DutyComments,
		ASP.ASP_Comments AS PersonComments,
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
	    SL.ScheduledPersonID	AS SchedulingPersonID,
		TD.dDateTime AS DutyDate,
		AD.AD_DutyStartTimeLocal AS StartDate,
		AD.AD_DutyEndTimeLocal AS EndDate,
		1 AS IsHomeTeam ,
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
		ASP.ASP_OverTwelveStatus as MarkOverTwelve,
		ASP.ASP_OverTwelveHrs AS OverTwelveHrs,
		ASP.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
		ASP.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
		ASP.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
		Asp.ASP_UnderElevenComments	AS UnderElevenComment,
		ASP_LeaveStartTimeSec	AS LeaveStartTime,
		ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
		ASP_CreatedBy AS CreatedBy,
		ASP_CreatedDate AS CreatedDate,
		ASP_UpdatedBy AS UpdatedBy,
		ASP_UpdatedDate AS UpdatedDate
  FROM  Allocations_ARCH AL WITH(NOLOCK)
  INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
  INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
  LEFT JOIN AllocationsScheduledPersons_ARCH ASP WITH(NOLOCK) ON AL.AL_AllocationsID=ASP_AllocationsID
														AND TD.ixDayInWeek = ASP.ASP_iDay
														AND SL.ScheduledPersonID = ASP.ASP_SchedulingPersonID
  LEFT JOIN AllocationsDuties_ARCH AD WITH(NOLOCK) ON AD.AD_AllocationsDutyID = ASP.ASP_AllocationsDutyID
  WHERE SL.scheduledType = 1
    AND SL.IsHomeTeam = 1 
	AND TD.dDateTime BETWEEn SL.StartDate and SL.EndDate
    AND EXISTS ( SELECT 1 FROM ArchivedWeeks AW WITH(NOLOCK) WHERE AW.WeekNumber = AL.AL_WeekNumber)
UNION ALL
 SELECT AL.AL_AllocationsID	AS AllocationID,
		ASP_AllocationsSPID AS AllocationSPID,
		AD_AllocationsDutyID AS AllocationDutyID,
		NULL 				AS StaffNumber ,
		CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				WHEN AD.AD_DutyType IN (8,11)
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
		CASE WHEN AD_DutyType IN (8,11)
			THEN ISNULL(ASP_LeaveDuration,0)
			ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
		AL.AL_WeekNumber AS WeekNumber,
		ASP.ASP_iDay AS iDay,
		ISNULL(AD_StartTimeSec,0) AS StartTime ,
		ISNULL(AD_EndTimeSec,0) AS EndTime,
		NULL	AS ActingGrade,
		ISNULL(AA.AAP_SortCode,SL.SortCode)			AS SortCode ,
		0		AS LeaveID,
		NULL AS ManualERR,
		AD.AD_Comments AS DutyComments,
		AA.AAP_Comments AS PersonComments,
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
	    SL.ScheduledPersonID	AS SchedulingPersonID,
		TD.dDateTime AS DutyDate,
		AD.AD_DutyStartTimeLocal AS StartDate,
		AD.AD_DutyEndTimeLocal AS EndDate,
		0 AS IsHomeTeam ,
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
		ASP_DutyTeamID AS DutyTeamID ,
		AD.AD_PlannedDuration AS PlannedDuration,  
		ASP.ASP_OverTwelveStatus as MarkOverTwelve,
		ASP.ASP_OverTwelveHrs AS OverTwelveHrs,
		ASP.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
		ASP.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
		ASP.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
		Asp.ASP_UnderElevenComments	AS UnderElevenComment,
		ASP_LeaveStartTimeSec	AS LeaveStartTime,
		ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
		ASP_CreatedBy AS CreatedBy,
		ASP_CreatedDate AS CreatedDate,
		ASP_UpdatedBy AS UpdatedBy,
		ASP_UpdatedDate AS UpdatedDate
	FROM Allocations_ARCH AS AL (nolock)
    INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
    INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
	LEFT JOIN AllocationsAddPersons_ARCH AA (nolock) on AL_AllocationsID = AA.AAP_AllocationsID  AND TD.ixDayInWeek = AA.AAP_iDay
										AND SL.ScheduledPersonID = AA.AAP_SchedulingPersonID
	LEFT JOIN AllocationsScheduledPersons_ARCH ASP (nolock) on AAP_AllocationsSPID = ASP_AllocationsSPID
	LEFT JOIN AllocationsDuties_ARCH AD (nolock) on AD_AllocationsDutyID = ASP_AllocationsDutyID
	LEFT JOIN Allocations_ARCH ALA (nolock) ON ALA.AL_AllocationsID = AD_AllocationsID AND ALA.AL_Status <> 9
	WHERE SL.scheduledType = 1
      AND SL.IsHomeTeam = 0
	  AND SL.IsAvailable = 1
	  AND TD.dDateTime BETWEEn SL.StartDate and SL.EndDate
	  AND NOT EXISTS ( SELECT 1 FROM ArchivedWeeks AW WITH(NOLOCK) WHERE AW.WeekNumber = AL.AL_WeekNumber)
	  AND ALA.AL_Status <> 9
UNION ALL
 SELECT AL.AL_AllocationsID	AS AllocationID,
		ASP_AllocationsSPID AS AllocationSPID,
		AD_AllocationsDutyID AS AllocationDutyID,
		NULL 				AS StaffNumber ,
		CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				WHEN AD.AD_DutyType IN (8,11)
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
		CASE WHEN AD_DutyType IN (8,11)
			THEN ISNULL(ASP_LeaveDuration,0)
			ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
		AL.AL_WeekNumber AS WeekNumber,
		ASP.ASP_iDay AS iDay,
		ISNULL(AD_StartTimeSec,0) AS StartTime ,
		ISNULL(AD_EndTimeSec,0) AS EndTime,
		NULL	AS ActingGrade,
		ISNULL(AA.AAP_SortCode,SL.SortCode)			AS SortCode ,
		0		AS LeaveID,
		NULL AS ManualERR,
		AD.AD_Comments AS DutyComments,
		AA.AAP_Comments AS PersonComments,
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
	    SL.ScheduledPersonID	AS SchedulingPersonID,
		TD.dDateTime AS DutyDate,
		AD.AD_DutyStartTimeLocal AS StartDate,
		AD.AD_DutyEndTimeLocal AS EndDate,
		0 AS IsHomeTeam ,
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
		ASP_DutyTeamID AS DutyTeamID ,
		AD.AD_PlannedDuration AS PlannedDuration,  
		ASP.ASP_OverTwelveStatus as MarkOverTwelve,
		ASP.ASP_OverTwelveHrs AS OverTwelveHrs,
		ASP.ASP_IsOverseasOverTwelve as IsOverseasOverTwelve,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 1 THEN 1 ELSE 0 END as IsUnderElevenBreak,
		ASP.ASP_CalculatedUnderElevenHrs AS CalculatedUnderElevenHrs,
		CASE WHEN Asp.ASP_UnderElevenBreakStatus = 2 THEN 1 ELSE 0 END AS IsUnderElevenBreakOverride,
		ASP.ASP_OverrideUnderElevenHrs AS OverrideUnderElevenHrs,
		Asp.ASP_UnderElevenComments	AS UnderElevenComment,
		ASP_LeaveStartTimeSec	AS LeaveStartTime,
		ASP_LeaveEndTimeSec		AS LeaveENDTime  ,
		ASP_CreatedBy AS CreatedBy,
		ASP_CreatedDate AS CreatedDate,
		ASP_UpdatedBy AS UpdatedBy,
		ASP_UpdatedDate AS UpdatedDate
	FROM Allocations_ARCH AS AL (nolock)
    INNER JOIN ScheduledPersonTeam_LINK SL (nolock) ON AL.AL_SchedulingTeamID = SL.TeamID
    INNER JOIN TimeDimension TD (nolock) ON TD.ixYearWeek = AL.AL_WeekNumber
	INNER JOIN AllocationsAddPersons_ARCH AA (nolock) on AL_AllocationsID = AA.AAP_AllocationsID  AND TD.ixDayInWeek = AA.AAP_iDay
										AND SL.ScheduledPersonID = AA.AAP_SchedulingPersonID
	LEFT JOIN AllocationsScheduledPersons_ARCH ASP (nolock) on AAP_AllocationsSPID = ASP_AllocationsSPID
	LEFT JOIN AllocationsDuties_ARCH AD (nolock) on AD_AllocationsDutyID = ASP_AllocationsDutyID
	LEFT JOIN Allocations_ARCH ALA (nolock) ON ALA.AL_AllocationsID = AD_AllocationsID AND ALA.AL_Status <> 9
	WHERE SL.scheduledType = 1
      AND SL.IsHomeTeam = 0
	  AND SL.IsAvailable = 0
	  AND TD.dDateTime BETWEEn SL.StartDate and SL.EndDate
	  AND NOT EXISTS ( SELECT 1 FROM ArchivedWeeks AW WITH(NOLOCK) WHERE AW.WeekNumber = AL.AL_WeekNumber)
	  AND ALA.AL_Status <> 9
GO

