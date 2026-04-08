SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 24-05-2022
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER PROCEDURE Usp_InsertUpdateHideStatsFlag 
	@teamId INT,
	@login VARCHAR(10)
AS
BEGIN
	IF exists (SELECT id FROM User_Web_Config WHERE (Login = @login) AND (SchedulingTeamId = @teamId))
		UPDATE User_Web_Config SET HideStats = HideStats ^ 1 WHERE (Login = @login) AND (SchedulingTeamId = @teamId)
	ELSE 
		INSERT INTO User_Web_Config (Login, SchedulingTeamId, HideStats) VALUES (@login, @teamId, 1)
END
GO