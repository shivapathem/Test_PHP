USE [Allocate7]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON

	IF NOT EXISTS(SELECT 1 FROM sys.columns 
			  WHERE Name = N'AllocationID'
			  AND Object_ID = Object_ID(N'[dbo].[Allocations_published_weeks]'))
	BEGIN
	 ALTER TABLE [dbo].[Allocations_published_weeks] ADD AllocationID [int] IDENTITY(1,1) NOT NULL
	END

	IF NOT EXISTS(SELECT 1 FROM sys.columns 
			  WHERE Name = N'IsPublished'
			  AND Object_ID = Object_ID(N'[dbo].[Allocations_published_weeks]'))
	BEGIN
	 ALTER TABLE [dbo].[Allocations_published_weeks] ADD IsPublished [int] NULL
	END
	
	IF NOT EXISTS(SELECT 1 FROM sys.columns 
			  WHERE Name = N'CreatedBy'
			  AND Object_ID = Object_ID(N'[dbo].[Allocations_published_weeks]'))
	BEGIN
	 ALTER TABLE [dbo].[Allocations_published_weeks] ADD CreatedBy [varchar](50) NULL
	END

	IF NOT EXISTS(SELECT 1 FROM sys.columns 
			  WHERE Name = N'CreatedDate'
			  AND Object_ID = Object_ID(N'[dbo].[Allocations_published_weeks]'))
	BEGIN
	 ALTER TABLE [dbo].[Allocations_published_weeks] ADD CreatedDate [datetime] NULL
	END

GO											
											



