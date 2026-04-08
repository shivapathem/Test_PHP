USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_PHLDebit]    Script Date: 29/03/2022 13:16:36 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE  [dbo].[usp_get_PHLDebit]
	-- Add the parameters for the stored procedure here navi
	@PHLStartDate varchar(50),
	@PHLENDDate varchar(50),
	@TeamID int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
    SET NOCOUNT ON;
    SELECT ref.Amount, la.dDate,
      la.SchedulingPersonID,
      la.Login,CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
      THEN (sd.Forename + ' ' + sd.Surname) ELSE (sd.PreferredForename + ' ' + sd.Surname) END
      ELSE sp.DisplayName END AS userDisplayName,sd.NetLogin,sd.StaffNumber
      FROM ref_LeaveApplications_Amounts as ref (nolock)
      INNER JOIN
      LeaveApplications as la ON la.ID = ref.ApplicationID
      INNER JOIN ScheduledPeople as sp ON sp.ScheduledPersonID =la.SchedulingPersonID
	  INNER JOIN ScheduledPersonTeam_LINK as spl on spl.ScheduledPersonID=la.SchedulingPersonID AND spl.TeamID=@TeamID AND spl.IsActive=1
      LEFT JOIN StaffDetails sd on sp.StaffDetailsID = sd.StaffID 
      WHERE 
      ref.LeaveTypeID = 2--PHL Type
	  AND la.SchedulingPersonID > 0
      AND ref.Amount > 0 
      AND la.Approved = 1 
      AND la.dDate >=CONVERT(DATE, @PHLStartDate, 102)
      AND la.dDate <=CONVERT(DATE, @PHLENDDate, 102)
	  order by la.dDate ASC
END

