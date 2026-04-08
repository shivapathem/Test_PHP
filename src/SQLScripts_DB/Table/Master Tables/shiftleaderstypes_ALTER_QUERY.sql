USE [Allocate7]
GO

/****** Object:  Table [dbo].[shiftleadertypes]    Script Date: 02/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[SchedulingTeamId]'
          AND Object_ID = Object_ID(N'[dbo].[shiftleadertypes]'))
BEGIN
	ALTER TABLE [dbo].[shiftleadertypes] ADD SchedulingTeamId INT NULL   

END
GO