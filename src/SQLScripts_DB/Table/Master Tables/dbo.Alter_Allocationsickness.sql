USE [Allocate7]
GO



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON



IF NOT EXISTS(SELECT 1 FROM sys.columns
WHERE Name = N'IsActive'
AND Object_ID = Object_ID(N'[dbo].[AllocationSickness]'))
BEGIN
ALTER TABLE [dbo].[AllocationSickness] ADD IsActive tinyint DEFAULT 1
END



GO