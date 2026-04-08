USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyTeamID'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Publish]'))
BEGIN
 ALTER TABLE [dbo].[Allocations_Publish] ADD DutyTeamID INT NULL
END

