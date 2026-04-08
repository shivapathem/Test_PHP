SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 24-05-2022
-- Description:	used to get staff count in a team
-- =============================================
CREATE OR ALTER PROCEDURE Usp_CountStaffInTeam 
	@teamId INT
AS
BEGIN
	select count(SP.ScheduledPersonID) AS CountStaff 
	from ScheduledPeople SP 
	INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = SP.ScheduledPersonID 
	WHERE SPTL.TeamID = @teamId AND SPTL.scheduledType = 1 AND GETDATE() between SPTL.StartDate AND SPTL.EndDate
END
GO
