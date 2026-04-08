USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetSchedulerList]    Script Date: 10/07/2025 13:19:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER           PROCEDURE [dbo].[usp_GetSchedulerList]
@AllocationSPID INT 
AS
BEGIN

 SET NOCOUNT ON;
 
  SELECT UD_DisplayName	AS DisplayName, 
		 RR.RoleName
   FROM Allocations AL
  INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
  INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = CASE WHEN ISNULL(ASP_DutyTeamID,0) = 0 THEN AL_SchedulingTeamID ELSE ASP_DutyTeamID END
  INNER JOIN UserDetails SP on SL.ScheduledPersonID = SP.UD_UserID
  inner join UserRoles UT on UT.UR_UserID = SP.UD_UserID and UT.UR_SchedulingTeamID = SL.TeamID
  inner join REF_Roles RR on UT.UR_RoleID = RR.RoleID
  WHERE GETDATE() between SL.StartDate and ISNULL(SL.EndDate,GETDATE())
    AND GETDATE() between UT.UR_StartDate and ISNULL(UT.UR_EndDate,GETDATE())
    AND ASP_AllocationsSPID = @AllocationSPID
    AND SL.IsHomeTeam = 0 
    AND SL.scheduledType = 0
    AND RR.RoleName IN ('Senior Scheduler','Scheduler')
  ORDER BY 1;

 END