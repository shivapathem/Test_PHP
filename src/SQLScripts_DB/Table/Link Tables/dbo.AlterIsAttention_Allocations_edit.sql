USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'IsAttention'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_edit]'))
BEGIN
	Alter Table [dbo].[Allocations_edit] ADD IsAttention BIT;

END
