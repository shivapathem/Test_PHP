USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
              WHERE Name = 'sentOptionValue' 
              AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
    ALTER TABLE LeaveApplications ADD sentOptionValue INT NOT NULL DEFAULT 0
END
GO
