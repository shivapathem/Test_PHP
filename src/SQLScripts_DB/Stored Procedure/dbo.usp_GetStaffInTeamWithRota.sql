USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetStaffInTeamWithRota]    Script Date: 25/08/2025 20:55:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 10-05-2022
-- Description:	used to return staff details as per team id
-- =============================================
CREATE OR ALTER   PROCEDURE [dbo].[usp_GetStaffInTeamWithRota] 
	@teamId INT
AS
BEGIN
	SET NOCOUNT ON;

    select SP.UD_DisplayName AS fullname,
	Sp.UD_StaffNumber as StaffNumber,
	SP.UD_UserID as ScheduledPersonID 
	from UserDetails SP  (NOLOCK)
	INNER JOIN RotaPeople RP ON RP.ScheduledPersonID = SP.UD_UserID
	INNER JOIN MasterRotas MR ON MR.RotaID = RP.RotaID
	--LEFT JOIN StaffDetails SD ON SD.StaffID = SP.StaffDetailsID
	WHERE MR.TeamID = @teamId
	GROUP BY sp.UD_DisplayName,sp.UD_StaffNumber, SP.UD_UserID
	ORDER BY SP.UD_DisplayName

END