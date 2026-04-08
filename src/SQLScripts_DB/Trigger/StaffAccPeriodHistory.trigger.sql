USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL    NVARCHAR(max)

--Check if the trigger already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT 1 FROM sys.triggers WHERE Name = 'StaffAccPeriodHistory_trigger')
    SET @strSQL = N'ALTER '
ELSE
    SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' TRIGGER StaffAccPeriodHistory_trigger
ON StaffAccPeriod
AFTER INSERT,UPDATE
AS
BEGIN 
	INSERT INTO StaffAccPeriodHistory(AccPeriodID, StaffID, AccType, StartDate, EndDate, StartWeek, EndWeek, IsActive, CreatedDate, LastModDate, LastModBy, History)
	SELECT INSERTED.AccPeriodID, INSERTED.StaffID, INSERTED.AccType, INSERTED.StartDate, INSERTED.EndDate, INSERTED.StartWeek, INSERTED.EndWeek, INSERTED.IsActive, INSERTED.CreatedDate, INSERTED.LastModDate, INSERTED.LastModBy, INSERTED.History
	FROM INSERTED
END

'

EXEC dbo.sp_executesql @strSQL
GO