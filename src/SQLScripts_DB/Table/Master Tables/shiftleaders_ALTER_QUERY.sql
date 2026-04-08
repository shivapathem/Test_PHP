USE [Allocate7]
GO

/****** Object:  Table [dbo].[shiftleaders]    Script Date: 02/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[shiftleaders]'))
BEGIN
ALTER TABLE [dbo].[shiftleaders] ADD ScheduledPersonID INT NULL DEFAULT 0
END
GO