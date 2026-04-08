Use [Allocate7]
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'TeampayStaffID' AND Object_ID = Object_ID(N'[dbo].[StaffDetails]'))
BEGIN
      ALTER TABLE StaffDetails add TeampayStaffID INT DEFAULT 0;
END
GO