/****** Object:  Table [dbo].[LockRequests]     Script Date: 10/02/2022 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[LockRequests]'))
BEGIN
ALTER TABLE [dbo].[LockRequests] Add ScheduledPersonID int default null
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'AllocationsInformation'
          AND Object_ID = Object_ID(N'[dbo].[LockRequests]'))
BEGIN
ALTER TABLE [dbo].[LockRequests] Add AllocationsInformation varchar(max) default null
END



GO