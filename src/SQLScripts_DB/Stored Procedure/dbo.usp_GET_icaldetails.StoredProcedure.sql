CREATE OR  ALTER   PROCEDURE [dbo].[usp_GET_icaldetails]
@teamid INT,
@ScheduledPersonID INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SELECT  ExternalEmail as PersonalEmail,InternalEmail as BBCEmail,
	CASE WHEN (sp.DisplayName IS NULL) 
	THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
	THEN (sd.Forename + '' + sd.Surname) ELSE (sd.PreferredForename + '' + sd.Surname)    END
	ELSE sp.DisplayName END AS FullName
	FROM ScheduledPersonTeam_LINK (NOLOCK) spt
	INNER JOIN ScheduledPeople (NOLOCK) sp ON spt.ScheduledPersonID = SP.ScheduledPersonID  
	LEFT JOIN StaffDetails (NOLOCK) sd ON sp.StaffDetailsID = sd.StaffID
	AND isnull(spt.EndDate,'9999-01-01') >= getdate()
	WHERE sp.ScheduledPersonID = @ScheduledPersonID
	AND spt.TeamID = @teamid AND spt.IsHomeTeam = 1 AND spt.scheduledType = 1;
END
   