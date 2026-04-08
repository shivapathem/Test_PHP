USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'LeaveID'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
	Alter Table [dbo].[LeaveApplications] ADD LeaveID INT DEFAULT 0;
END


