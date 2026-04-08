USE [BBCSchedules]
GO

/****** Object:  View [dbo].[vAllocations_Jobs]    Script Date: 29/08/2025 21:42:03 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


CREATE OR ALTER   view [dbo].[vAllocations_Jobs]
as
 SELECT AJ_AllocationsDutyID AS AllocationDutyID,
		AJ.AJ_AllocateJobID AS AllocateJobID,
		AJ_ProgrammeID AS Programme,
		AJ.AJ_Contact AS Contact,
		AJ.AJ_Location AS [Location],
		AJ.AJ_JobName AS JobName,
		AJ.AJ_JobStartTimeSec StartTime,
		AJ_JobEndTimeSec EndTime,
		AJ.AJ_JobBGColour AS JobBackColour,
		AJ.AJ_JobFontColour AS JobFontColour,
		AJ.AJ_Comments AS Comments,
		AJ.AJ_MasterJobID AS MasterJobID,
		CASE WHEN AJ_JobStatus = 0 THEN 1 ELSE 0 END AS UnAllocated,
		CASE WHEN AJ_JobStatus = 9 THEN 0 ELSE 1 END AS  IsActive,
		AJ_IsEditedJobAttention isEdited,
		AJ.AJ_ProgrammeID AS ProgrammeID,
		AJ.AJ_JobInfo AS JobInfo,
	    CASE WHEN DATEDIFF(DAY,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal) > 0 THEN 1 ELSE 0 END AS  aftermidnight
  FROM AllocationsJobs AJ WITH(NOLOCK)	
 UNION
 SELECT AJ_AllocationsDutyID AS AllocationDutyID,
		AJ.AJ_AllocateJobID AS AllocateJobID,
		AJ_ProgrammeID AS Programme,
		AJ.AJ_Contact AS Contact,
		AJ.AJ_Location AS [Location],
		AJ.AJ_JobName AS JobName,
		AJ.AJ_JobStartTimeSec StartTime,
		AJ_JobEndTimeSec EndTime,
		AJ.AJ_JobBGColour AS JobBackColour,
		AJ.AJ_JobFontColour AS JobFontColour,
		AJ.AJ_Comments AS Comments,
		AJ.AJ_MasterJobID AS MasterJobID,
		CASE WHEN AJ_JobStatus = 0 THEN 1 ELSE 0 END AS UnAllocated,
		CASE WHEN AJ_JobStatus = 9 THEN 0 ELSE 1 END AS  IsActive,
		AJ_IsEditedJobAttention isEdited,
		AJ.AJ_ProgrammeID AS ProgrammeID,
		AJ.AJ_JobInfo AS JobInfo,
	    CASE WHEN DATEDIFF(DAY,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal) > 0 THEN 1 ELSE 0 END AS  aftermidnight
   FROM	AllocationsJobs_Arch AJ WITH(NOLOCK)

GO

