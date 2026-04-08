USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsAndJobsIndividual]    Script Date: 3/5/2026 12:24:45 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_get_ReadAllocationsAndJobsIndividual]
@StartWeek varchar(100),
@EndWeek varchar(100),
@SchedulingPersonId  INT,
@NetLogin varchar(100)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	SELECT sp.UD_UserID                                           AS StaffID,
		   sp.UD_DisplayName									  AS FullName,
		   sp.UD_StaffNumber									  AS StaffNumber,
		   A.schedulingteamid									  AS schedulingTeamId,
		   sp.UD_InternalEmail                                    AS Email,
		   spl.sortcode											  AS SortCode,
		   A.weeknumber 										  AS WeekNumber,
		   A.iday 												  AS iDay,
		   A.id                                                   AS DutyID,
		   A.dutyname                                             AS DutyName,
		   A.starttime                                            AS StartTime,
		   A.endtime                                              AS EndTime,
		   A.duration                                             AS Duration,
		   AJ.id                                                  AS JobID,
		   AJ.starttime                                           AS JobStartTime,
		   AJ.endtime                                             AS JobEndTime,
		   AJ.jobname                                             AS JobName,
		   ad.dDate                                               AS HiddenDays
	FROM   allocations_publish A (nolock)
	INNER  JOIN UserDetails AS sp (nolock) ON sp.UD_UserID = A.schedulingpersonid
	INNER  JOIN scheduledpersonteam_link (nolock) AS spl ON sp.UD_UserID = spl.scheduledpersonid
				AND spl.teamid = A.schedulingteamid
	 LEFT  JOIN allocations_jobs_publish AJ ON A.ID = AJ.allocationid
     LEFT  JOIN AllocationsHiddenDays AD on ad.dDate = a.DutyDate and ad.SchedulingTeamId = a.SchedulingTeamId
	        AND ad.isRestricted = 1
	WHERE   A.weeknumber >= @StartWeek 
	  AND   A.weeknumber <= @EndWeek 
	  AND   a.DutyDate between spl.StartDate and spl.EndDate
	  and   spl.scheduledType = 1
	  AND  ( A.schedulingpersonid = @SchedulingPersonId  OR sp.UD_NetLogin = @NetLogin )
	ORDER  BY sp.UD_DisplayLastName,
			  sp.UD_DisplayFirstName,
			  A.weeknumber,
			  A.iday 
	
	END