USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF  EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'isAttention'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
 ALTER TABLE Allocations ALTER COLUMN isAttention INT 
END

GO