USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[Usp_GetStaffInTeam]    Script Date: 22/08/2025 21:59:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 24-05-2022
-- Description:	Used to get staff name and staff number of team
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[Usp_GetStaffInTeam] 
	@teamId INT,
	@login VARCHAR(10),
	@sDate VARCHAR(10),
	@eDate VARCHAR(10)
AS
BEGIN
	IF(@login = 0)
	BEGIN
		SELECT sp.UD_DisplayName AS fullname, Sp.UD_UserID as ScheduledPersonID FROM UserDetails SP 
		INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = SP.UD_UserID 
		WHERE (SPTL.TeamID = @teamId) AND SPTL.StartDate <= @eDate AND SPTL.EndDate >= @sDate and IsHomeTeam = 1 
		ORDER BY UD_DisplayName
	END
	ELSE
	BEGIN
		SELECT sp.UD_DisplayName AS fullname FROM UserDetails SP 
		INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = SP.UD_UserID 
		WHERE (SPTL.TeamID = @teamId) AND SPTL.StartDate <= @eDate AND SPTL.EndDate >= @sDate and IsHomeTeam = 1 
		ORDER BY UD_DisplayName
	END
END