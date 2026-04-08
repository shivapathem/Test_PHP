Use [Allocate7]
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_import_TempStaffAccPeriod] 
AS
BEGIN

TRUNCATE TABLE Allocate7.dbo.TempStaffAccPeriod

	INSERT INTO Allocate7.dbo.TempStaffAccPeriod (NetLogin, StaffID, StartWeek, EndWeek, StartDate, EndDate, Period, AccGroupID)
		SELECT NetLogin, StaffID, StartWeek, EndWeek, StartDate, EndDate, Period, AccGroupID FROM Allocatelink.dbo.TP_A7_TempStaffAccPeriod

END

GO

EXEC usp_import_TempStaffAccPeriod

GO