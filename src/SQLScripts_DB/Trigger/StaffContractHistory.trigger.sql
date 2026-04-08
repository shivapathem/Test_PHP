USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL    NVARCHAR(max)

--Check if the trigger already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT 1 FROM sys.triggers WHERE Name = 'StaffContractHistory_trigger')
    SET @strSQL = N'ALTER '
ELSE
    SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' TRIGGER StaffContractHistory_trigger
ON StaffContract
AFTER INSERT,UPDATE
AS
BEGIN 
	INSERT INTO StaffContractHistory(ContractID, StaffID, EmpNumber, StartDate, EndDate, ActualEndDate, DepartmentID, OrgID, OrgPositionID, CostCode, JobTitle, Grade, ActingGrade, IsPartTime, EFT, ContractCode, UPACode, CreatedDate, LastModDate, LastModBy, History, IsActive, EmpSubGroup)
	SELECT INSERTED.ContractID, INSERTED.StaffID, INSERTED.EmpNumber, INSERTED.StartDate, INSERTED.EndDate, INSERTED.ActualEndDate, INSERTED.DepartmentID, INSERTED.OrgID, INSERTED.OrgPositionID, INSERTED.CostCode, INSERTED.JobTitle, INSERTED.Grade, INSERTED.ActingGrade, INSERTED.IsPartTime, INSERTED.EFT, INSERTED.ContractCode, INSERTED.UPACode, INSERTED.CreatedDate, INSERTED.LastModDate, INSERTED.LastModBy, INSERTED.History, INSERTED.IsActive, INSERTED.EmpSubGroup
	FROM INSERTED
END

'

EXEC dbo.sp_executesql @strSQL
GO