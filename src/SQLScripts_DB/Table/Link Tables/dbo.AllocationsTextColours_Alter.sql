USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Adding DivisionID column if it does not exist
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'DivisionID' AND Object_ID = Object_ID(N'[dbo].[AllocationsTextColours]'))
BEGIN
    ALTER TABLE [dbo].[AllocationsTextColours] ADD DivisionID int
END
GO
