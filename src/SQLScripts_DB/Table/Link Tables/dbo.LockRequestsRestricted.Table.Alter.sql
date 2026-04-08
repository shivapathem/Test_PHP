/****** Object:  Table [dbo].[LockRequestsRestricted]     Script Date: 10/02/2022 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[LockRequestsRestricted]'))
BEGIN
ALTER TABLE [dbo].[LockRequestsRestricted] Add ScheduledPersonID int default null
END



GO