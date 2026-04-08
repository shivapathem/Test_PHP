USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[Usp_InsertUpdateHideStaffFlag]    Script Date: 20/03/2024 14:50:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 24-05-2022
-- Description:	Used to update hidestafflist flat for reports
-- =============================================
CREATE OR ALTER PROCEDURE Usp_InsertUpdateHideStaffFlag
	@teamId INT,
	@login VARCHAR(10)
AS
BEGIN
	if exists (SELECT id FROM User_Web_Config WHERE (Login = @login) AND (SchedulingTeamId = @teamId))
		UPDATE  User_Web_Config SET HideStaffList = isNull(HideStaffList, 0) ^ 1 WHERE (Login = @login) AND (SchedulingTeamId = @teamId)
	ELSE 
		INSERT INTO User_Web_Config (SchedulingTeamId, Login, HideStaffList) VALUES (@teamId, @login, 1)
END
