USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_WhoISInDetails]    Script Date: 3/13/2026 4:48:07 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER        PROCEDURE  [dbo].[usp_Get_WhoISInDetails]
AS   
BEGIN  
  
SET NOCOUNT ON  
  
 SELECT Distinct  
  ScheduledPersonID,  
  schedulingTeamId,  
  schedulingTeamName,  
  DisplayLastName,   
  DisplayFirstName,   
  StartTime,  
  EndTime,  
  DutyName,  
  PreviousDayDuty,
  LeaveStartTime,
  LeaveEndTime
  FROM (  
   SELECT ISNULL(AST.schedulingTeamName, st.schedulingTeamName) AS schedulingTeamName,  
    sp.UD_DisplayLastName AS DisplayLastName,   
    sp.UD_DisplayFirstName AS DisplayFirstName,   
    AD.AD_StartTimeSec AS StartTime,  
    AD.AD_EndTimeSec AS EndTime,  
    AD.AD_DutyName AS DutyName,  
    pal.DutyName PreviousDayDuty,  
    sp.UD_UserID AS ScheduledPersonID,  
    ISNULL(AST.schedulingTeamId, st.schedulingTeamId) AS schedulingTeamId,  
    rank() over (partition by aSP.ASP_SchedulingPersonID,AD.AD_DutyDate order by (CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL_SchedulingTeamID THEN 0 ELSE 1 END) desc) AS rankcol,
	aSP.ASP_LeaveStartTimeSec AS LeaveStartTime,
	ASP.ASP_LeaveEndTimeSec AS LeaveEndTime
     FROM Allocations AL WITH(NOLOCK)
	INNER JOIN AllocationsScheduledPersons ASP WITH(NOLOCK) ON AL.AL_AllocationsID=ASP.ASP_AllocationsID
	INNER JOIN Allocationsduties AD WITH(NOLOCK) ON AL.AL_AllocationsID=AD.AD_AllocationsID and ASP.ASP_AllocationsDutyID=AD.AD_AllocationsDutyID
    INNER JOIN TimeDimension TD WITH(NOLOCK) on TD.ixYearWeek = al.AL_WeekNumber and td.ixDayInWeek = ASP.ASP_iDay 
    INNER JOIN UserDetails SP WITH(NOLOCK) on sp.UD_UserID = ASP.ASP_SchedulingPersonID  
    INNER JOIN schedulingTeams st WITH(NOLOCK) on st.schedulingTeamId = al.AL_SchedulingTeamID  
    LEFT JOIN schedulingTeams AST WITH(NOLOCK) on ast.schedulingTeamId = ASP.ASP_DutyTeamID  
    INNER JOIN ( select distinct TeamID   
       from ScheduledPersonTeam_LINK  WITH(NOLOCK)
      where isWhosIn = 1  
        and getdate() between StartDate and isnull(EndDate , getdate() )  
       --and scheduledType = 1  
        ) SL on sl.TeamID = al.AL_SchedulingTeamID
    LEFT JOIN (  SELECT Ad.ad_DutyName AS DutyName,  
         ASP.ASP_SchedulingPersonID AS SchedulingPersonID ,
         AL.al_SchedulingTeamId AS SchedulingTeamId,  
         rank() over (partition by aSP.ASP_SchedulingPersonID,aD.AD_dutydate order by (CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL_SchedulingTeamID THEN 0 ELSE 1 END) desc) AS PALRankcol  
       FROM Allocations AL  WITH(NOLOCK)
	  INNER JOIN AllocationsScheduledPersons ASP WITH(NOLOCK) ON AL.AL_AllocationsID=ASP.ASP_AllocationsID
	  INNER JOIN Allocationsduties AD WITH(NOLOCK) ON AL.AL_AllocationsID=AD.AD_AllocationsID  and  ASP.ASP_AllocationsDutyID=AD.AD_AllocationsDutyID
      INNER JOIN TimeDimension TD WITH(NOLOCK) on TD.ixYearWeek = al.al_WeekNumber and td.ixDayInWeek = ad.AD_iDay  
      INNER JOIN ( select distinct TeamID   
          from ScheduledPersonTeam_LINK  WITH(NOLOCK)
         where isWhosIn = 1  
          and dateadd(day, -1 ,cast(getdate() as date) ) BETWEEN StartDate AND isnull(EndDate , getdate() )
          --and scheduledType = 1  
          ) SL on sl.TeamID = al.AL_SchedulingTeamID  
      WHERE td.dDateTime =  dateadd(day, -1 ,cast(getdate() as date) )  
        AND (AD.AD_DutyName like '%Sick%'  
        or AD.AD_DutyName like '%Leave%'  
        or AD.AD_DutyName like '%Absent%' )  
     ) PAL on ASP.ASP_SchedulingPersonID = pal.SchedulingPersonID  
       and al.AL_SchedulingTeamId = pal.SchedulingTeamId and PALRankcol = 1  
    WHERE td.dDateTime =  cast(getdate() as date)  
   and ( AD.AD_Duration <> 0 or     (     aD.AD_DutyName like '%Sick%'  
            or AD.AD_DutyName like '%Leave%'  
            or AD.AD_DutyName like '%Absent%'   
             )     
    )  
   ) FD where rankcol = 1  
   ORDER BY schedulingTeamName, StartTime, EndTime, DisplayFirstName  
    
END