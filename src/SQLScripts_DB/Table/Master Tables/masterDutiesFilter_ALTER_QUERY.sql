USE [Allocate7]
GO

/****** Object:  Table [dbo].[MasterDutiesFilter]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[TeamID]'
          AND Object_ID = Object_ID(N'[dbo].[MasterDutiesFilter]'))
BEGIN
ALTER TABLE [dbo].[MasterDutiesFilter] ADD TeamID INT NOT NULL DEFAULT 0

END
GO