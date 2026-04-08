USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_AllocationsDetailsByAllocationId]    Script Date: 21/01/2022 00:59:07 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_AllocationsDetailsByAllocationId]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_AllocationsDetailsByAllocationId]
@allocationId INT,
@isEdited INT

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	IF(@isEdited != 1)
	BEGIN
		SELECT a.ID as AllocationID,a.MasterDutyId,a.DutyName, a.WeekNumber, a.iDay, a.StartTime, a.EndTime, a.SchedulingTeamId, a.SchedulingPersonID, a.isEdited, a.PersonComments, sd.StaffNumber,
			CASE WHEN ISNULL(sp.DisplayName,'''') != '''' THEN sp.DisplayName
				WHEN (ISNULL(sp.DisplayName,'''') = '''' and ISNULL(sd.PreferredForename,'''') != '''') THEN sd.PreferredForename + N''  '' + sd.Surname
				ELSE sd.Forename + N''  '' + sd.Surname END AS FullName,
		sp.DisplayFirstName,
		sp.DisplayLastName,ec.EstablishCode,ec.EstablishCodeDescription,ec.EstablishCodeId,a.StartDate,a.Duration,sp.ScheduledPersonID,sp.StaffDetailsID
		FROM Allocations a
		INNER JOIN ScheduledPersonTeam_LINK spt
		ON a.SchedulingPersonID = spt.ScheduledPersonID AND a.SchedulingTeamId = spt.TeamID
		INNER JOIN ScheduledPeople sp
		ON sp.ScheduledPersonID = spt.ScheduledPersonID
		INNER JOIN schedulingTeams st on st.schedulingTeamId = spt.TeamID and st.isActive = 1
		LEFT JOIN EstablishCode ec on ec.EstablishCodeId = st.establishCodeID
		LEFT JOIN StaffDetails sd
		ON sd.StaffID = sp.StaffDetailsID
		WHERE a.ID = @allocationId
	END
	ELSE
	BEGIN
		SELECT a.ID as AllocationID,a.MasterDutyId,ae.DutyName, ae.WeekNumber, ae.iDay, ae.StartTime, ae.EndTime, ae.SchedulingTeamId, CASE WHEN (ae.SchedulingPersonID IS NULL OR ae.SchedulingPersonID=0)
		THEN a.SchedulingPersonID ELSE ae.SchedulingPersonID END AS SchedulingPersonID, a.isEdited, a.PersonComments, sd.StaffNumber,
			CASE WHEN ISNULL(sp.DisplayName,'''') != '''' THEN sp.DisplayName
				WHEN (ISNULL(sp.DisplayName,'''') = '''' and ISNULL(sd.PreferredForename,'''') != '''') THEN sd.PreferredForename + N''  '' + sd.Surname
				ELSE sd.Forename + N''  '' + sd.Surname END AS FullName,
		sp.DisplayFirstName,sp.DisplayLastName,ec.EstablishCode,ec.EstablishCodeDescription,ec.EstablishCodeId,ae.StartDate,ae.Duration,sp.ScheduledPersonID,sp.StaffDetailsID
		FROM Allocations_edit ae
		LEFT JOIN Allocations a
		ON a.ID = ae.AllocationID
		INNER JOIN ScheduledPersonTeam_LINK spt
		ON ae.SchedulingPersonID = spt.ScheduledPersonID AND ae.SchedulingTeamId = spt.TeamID
		INNER JOIN ScheduledPeople sp
		ON sp.ScheduledPersonID = spt.ScheduledPersonID
		INNER JOIN schedulingTeams st on st.schedulingTeamId = spt.TeamID and st.isActive = 1
		LEFT JOIN EstablishCode ec on ec.EstablishCodeId = st.establishCodeID
		LEFT JOIN StaffDetails sd
		ON sd.StaffID = sp.StaffDetailsID
		WHERE ae.AllocationID = @allocationId
	END
END
'

EXEC dbo.sp_executesql @strSQL

GO
