SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 24-05-2022
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER PROCEDURE Usp_InsertUpdateMasterDutiesHide 
	@teamId INT,
	@dutyId INT
AS
BEGIN
	if exists (SELECT ID FROM MasterDutiesHide WHERE (DepartmentID = @teamId) AND (AllocMasterDutyID = @dutyId))
		UPDATE  MasterDutiesHide SET isHidden = isHidden ^ 1 WHERE (DepartmentID = @teamId) AND (AllocMasterDutyID = @dutyId)
	ELSE 
		INSERT INTO MasterDutiesHide (DepartmentID, AllocMasterDutyID, isHidden) VALUES (@teamId, @dutyId, 1)
END
GO