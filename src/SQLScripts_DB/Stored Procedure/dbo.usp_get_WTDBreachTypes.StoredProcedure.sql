USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_WTDBreachTypes]    Script Date: 08/09/2025 21:28:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER        PROCEDURE [dbo].[usp_get_WTDBreachTypes]
@dutyID  INT,
@wiadOption INT

AS
BEGIN
  SET NOCOUNT ON;

 IF(@wiadOption=1)
      BEGIN
			SELECT DISTINCT
			AD_DutyName + ' ('
			+ Cast(Cast(Cast((Isnull(AD.AD_duration, 0)-Isnull(AD.AD_dutyBreakTime, 0)) AS FLOAT)/
			Cast(3600 AS FLOAT) AS DECIMAL(5, 2)) AS VARCHAR)
			+ ' Hours)'                                                            AS
			Dutyname,
			(SELECT schedulingTeamName
			 FROM   Schedulingteams
			 WHERE  schedulingTeamId = Isnull(ASP.ASP_DutyTeamID, AL.AL_SchedulingTeamID)) AS
			TeamName,
			WT."Rule"                                                              AS
			BreachType
			FROM   Allocations AL WITH (NOLOCK)
			       INNER JOIN AllocationsScheduledPersons ASP WITH (NOLOCK)
				    ON AL.AL_AllocationsID = ASP.ASP_AllocationsID 
			      INNER JOIN AllocationsDuties AD WITH (NOLOCK) 
				    ON ASP.ASP_AllocationsDutyID= AD.AD_AllocationsDutyID 
				   INNER JOIN Working_time_directive WTD
						   ON ASP.ASP_SchedulingPersonID = WTD.SchedulingPersonID
				   INNER JOIN Wtd_types WT
						   ON WTD.BreachType = WT.ID
			WHERE  AD.AD_AllocationsDutyID = @dutyID
				   AND AD.AD_DutyDate BETWEEN WTD.StartDate AND WTD.EndDate
				   AND WTD.IsApproved < 2 
	END
ELSE

       BEGIN
			SELECT DISTINCT
					AD.AD_dutyname + ' ('
					+ Cast(Cast(Cast((Isnull(AD.AD_duration, 0)-Isnull(AD.AD_dutyBreakTime, 0)) AS FLOAT)/
					Cast(3600 AS FLOAT) AS DECIMAL(5, 2)) AS VARCHAR)
					+ ' Hours)'                                                            AS
					Dutyname,
					(SELECT schedulingTeamName
					 FROM   Schedulingteams WITH (NOLOCK)
					 WHERE  schedulingTeamId = Isnull(ASP.ASP_DutyTeamID, AL.AL_SchedulingTeamID)) AS
					TeamName
			FROM   Allocations AL WITH (NOLOCK) 
			         INNER JOIN  AllocationsScheduledPersons  ASP WITH (NOLOCK) 
				    ON AL.AL_AllocationsID = ASP.ASP_AllocationsID 
					 inner join AllocationsDuties AD WITH (NOLOCK)
				    ON ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID      
			WHERE  AD.AD_AllocationsDutyID = @dutyID 
	   END

  END