USE [AllocateLink]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'comments' AND Object_ID = Object_ID(N'[AllocateLink].[dbo].[TP_A7_StaffConfig_Processed]'))
BEGIN
  	ALTER TABLE [AllocateLink].[dbo].[TP_A7_StaffConfig_Processed] add comments VARCHAR(500);
END


GO