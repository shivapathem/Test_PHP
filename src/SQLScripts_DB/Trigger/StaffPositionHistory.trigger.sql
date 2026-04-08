USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL    NVARCHAR(max)

--Check if the trigger already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT 1 FROM sys.triggers WHERE Name = 'StaffPositionHistory_trigger')
    SET @strSQL = N'ALTER '
ELSE
    SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' TRIGGER StaffPositionHistory_trigger
ON StaffPosition
AFTER INSERT,UPDATE
AS
BEGIN 
	INSERT INTO StaffPositionHistory(PositionID, StaffID, EmpNumber, StartDate, EndDate, CostCode, Level2, Level3, Level4, Level5, JobTitle, EmployeeGroup, EmployeeSubGroup, SubActingStatus, SubstantiveCostCode, ActualPosition, HROrganisation, FreelancerMainID, IsActive, CreatedDate, LastModDate, LastModBy, History)
	SELECT INSERTED.PositionID, INSERTED.StaffID, INSERTED.EmpNumber, INSERTED.StartDate, INSERTED.EndDate, INSERTED.CostCode, INSERTED.Level2, INSERTED.Level3, INSERTED.Level4, INSERTED.Level5, INSERTED.JobTitle, INSERTED.EmployeeGroup, INSERTED.EmployeeSubGroup, INSERTED.SubActingStatus, INSERTED.SubstantiveCostCode, INSERTED.ActualPosition, INSERTED.HROrganisation, INSERTED.FreelancerMainID, INSERTED.IsActive, INSERTED.CreatedDate, INSERTED.LastModDate, INSERTED.LastModBy, INSERTED.History
	FROM INSERTED
END

'

EXEC dbo.sp_executesql @strSQL
GO