USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_XmasAllocationAndRota]    Script Date: 24/09/2025 13:29:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE  OR  ALTER      PROCEDURE [dbo].[usp_fetch_XmasAllocationAndRota]  
@TeamID INT,  
@Year INT  
  
AS  
BEGIN  
 -- SET NOCOUNT ON added to prevent extra result sets from  
 -- interfering with SELECT statements.  
 SET NOCOUNT ON;  
   
  
 SELECT WeekNumber,   
        FullName,   
     NetLogin,   
     SchedulingTeamId,  
     DutyName,  
     SchedulingPersonID,   
     DOTW,   
     Duration,   
     StartTime,  
     EndTime,  
     IsTemplate  
 FROM  
 (  
 SELECT a.WeekNumber AS WeekNumber,   
      sp.UD_DisplayName  FullName,   
   sp.uD_NetLogin AS NetLogin,   
   spl.TeamID               AS SchedulingTeamId,  
   a.DutyName               AS DutyName,  
   a.SchedulingPersonID     AS SchedulingPersonID,   
   a.iDay                   AS DOTW,   
   a.Duration               AS Duration,   
   a.StartTime              AS StartTime,  
   a.EndTime                AS EndTime,  
   0                        AS IsTemplate,  
   rank() over (partition by a.weeknumber,a.iday order by (case when a.dutyname <> 'U' then 1 else 0 end) desc, a.ishometeam desc) AS rankcol  
    FROM dbo.Allocations_Publish as a WITH (NOLOCK)      
   INNER JOIN UserDetails as sp WITH (NOLOCK) on sp.UD_UserID = a.SchedulingPersonID  
   INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.UD_UserID = spl.ScheduledPersonID   
   INNER JOIN (select ixYearWeek as WeekNumber, ixDayInWeek as iDay  
     FROM TimeDimension  WITH (NOLOCK)
     WHERE ( dDateTime between cast(CAST(@year-3 AS VARCHAR)+'-12-23' AS DATE) and CAST(CAST(@year-2 AS VARCHAR)+'-01-01' AS DATE)   
         OR dDateTime between cast(CAST(@year-2 AS VARCHAR)+'-12-23' AS DATE) and CAST(CAST(@year-1 AS VARCHAR)+'-01-01' AS DATE)  
         OR dDateTime between cast(CAST(@year-1 AS VARCHAR)+'-12-23' AS DATE) and CAST(CAST(@year AS VARCHAR)+'-01-01' AS DATE))  
    ) AS TD ON a.weeknumber = TD.WeekNumber AND a.iDay = TD.iDay       
  WHERE spl.scheduledType = 1  
    AND spl.TeamID = @TeamID  
    AND spl.IsHomeTeam = 1  
    And CAST(getdate() AS DATE) BETWEEN ISNULL(spl.StartDate,CAST(getdate() AS DATE)) AND ISNULL(spl.EndDate,CAST(getdate() AS DATE))       
  ) FD where rankcol = 1  
  order by FullName, WeekNumber, DOTW  
    
 END