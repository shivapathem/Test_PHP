USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocations]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'JobNameAll'
          AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
ALTER TABLE [dbo].[AutoPagesFilters] ADD JobNameAll INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'JobLabelAll'
          AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
ALTER TABLE [dbo].[AutoPagesFilters] ADD JobLabelAll INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyTime'
          AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
ALTER TABLE [dbo].[AutoPagesFilters] ADD DutyTime INT NULL DEFAULT 0
END

GO