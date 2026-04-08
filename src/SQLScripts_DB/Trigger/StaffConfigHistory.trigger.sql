USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL    NVARCHAR(max)

--Check if the trigger already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT 1 FROM sys.triggers WHERE Name = 'StaffConfigHistory_trigger')
    SET @strSQL = N'ALTER '
ELSE
    SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' TRIGGER StaffConfigHistory_trigger
ON StaffConfig
AFTER INSERT,UPDATE
AS
BEGIN 
	INSERT INTO StaffConfigHistory(ConfigID, StaffID, StartDate, EndDate, EDPMinimum, EDPMinimumExcBreaks, PartTimeEDP, AccGroupID, AveDayLen, AccDays, TermsCondsVersionID, PaymentTypeID, BreaksGroupID, ManualEDP, ActivityTypeID, ActivityType, CompExpiry, TOILExpiry, Under11TOILExpiry, Over12TOILExpiry, AutoEDPTOIL, SendEmails, IsActive, CreatedDate, LastModDate, LastModBy, History)
	SELECT INSERTED.ConfigID, INSERTED.StaffID, INSERTED.StartDate, INSERTED.EndDate, INSERTED.EDPMinimum, INSERTED.EDPMinimumExcBreaks, INSERTED.PartTimeEDP, INSERTED.AccGroupID, INSERTED.AveDayLen, INSERTED.AccDays, INSERTED.TermsCondsVersionID, INSERTED.PaymentTypeID, INSERTED.BreaksGroupID, INSERTED.ManualEDP, INSERTED.ActivityTypeID, INSERTED.ActivityType, INSERTED.CompExpiry, INSERTED.TOILExpiry, INSERTED.Under11TOILExpiry, INSERTED.Over12TOILExpiry, INSERTED.AutoEDPTOIL, INSERTED.SendEmails, INSERTED.IsActive, INSERTED.CreatedDate, INSERTED.LastModDate, INSERTED.LastModBy, INSERTED.History
	FROM INSERTED
END

'

EXEC dbo.sp_executesql @strSQL
GO