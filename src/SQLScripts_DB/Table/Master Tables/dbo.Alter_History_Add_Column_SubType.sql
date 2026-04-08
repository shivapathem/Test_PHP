USE [Allocate7]
GO



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON



IF NOT EXISTS(SELECT 1 FROM sys.columns
WHERE Name = N'HistorySubType'
AND Object_ID = Object_ID(N'[dbo].[History]'))
BEGIN
ALTER TABLE [dbo].[History] ADD HistorySubType varchar(4) NULL
END



GO