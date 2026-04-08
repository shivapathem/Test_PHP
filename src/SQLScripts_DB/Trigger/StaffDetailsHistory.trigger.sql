USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL    NVARCHAR(max)

--Check if the trigger already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT 1 FROM sys.triggers WHERE Name = 'StaffDetailsHistory_trigger')
    SET @strSQL = N'ALTER '
ELSE
    SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' TRIGGER StaffDetailsHistory_trigger
ON StaffDetails
AFTER INSERT,UPDATE
AS
BEGIN 
	INSERT INTO StaffDetailsHistory(StaffID, EmpNumber, StaffNumber, Title, Initials, Surname, Forename, PreferredForename, 
		SecondName, Room, SubLocation, BuildingCode, OfficeExtension, OfficeMobile, NetLogin, InternalEmail, IsLeaver, LeaveDate, 
		LeaveDateSat, CreatedDate, ManualEntry, History)
	SELECT INSERTED.StaffID, INSERTED.EmpNumber, INSERTED.StaffNumber, INSERTED.Title, INSERTED.Initials, INSERTED.Surname, 
		INSERTED.Forename, INSERTED.PreferredForename, INSERTED.SecondName, INSERTED.Room, INSERTED.SubLocation, 
		INSERTED.BuildingCode, INSERTED.OfficeExtension, INSERTED.OfficeMobile, INSERTED.NetLogin, INSERTED.InternalEmail, 
		INSERTED.IsLeaver, INSERTED.LeaveDate, INSERTED.LeaveDateSat, INSERTED.CreatedDate, INSERTED.ManualEntry, 
		INSERTED.History
	FROM INSERTED
END

'

EXEC dbo.sp_executesql @strSQL
GO