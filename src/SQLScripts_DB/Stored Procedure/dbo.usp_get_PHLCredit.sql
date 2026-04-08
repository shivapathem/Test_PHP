USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_PHLCredit]    Script Date: 24/06/2022 17:06:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_get_PHLCredit]
	-- Add the parameters for the stored procedure here navi
	@PHLStartDate varchar(50),
	@PHLENDDate varchar(50),
	@TeamID int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
    SET NOCOUNT ON;
    SELECT 
	  la.iYear, 
      la.dDate,
      la.SchedulingPersonID,
	  la.SchedulingTeamid,
      la.PHL as Amount,
      la.Comments,
	  la.TimeDemensionID,
	  la.ID,
      CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
      THEN (sd.Forename + ' ' + sd.Surname) ELSE (sd.PreferredForename + ' ' + sd.Surname) END
      ELSE sp.DisplayName END AS userDisplayName,sd.NetLogin,sd.StaffNumber
      FROM LeaveAllocation  as la
      INNER JOIN ScheduledPeople as sp ON sp.ScheduledPersonID =la.SchedulingPersonID
      LEFT JOIN StaffDetails sd on sp.StaffDetailsID = sd.StaffID 
      WHERE 
      la.PHL is not null 
	  AND la.SchedulingPersonID > 0
      AND la.TimeDemensionID is not null AND la.is_PHL=1
      AND la.dDate <=CONVERT(DATE, @PHLENDDate , 102)
      AND la.dDate >=CONVERT(DATE, @PHLStartDate, 102)
	  AND la.SchedulingTeamid =@TeamID
	  AND la.IsActive = 1
      ORDER BY dDate ASC
END

