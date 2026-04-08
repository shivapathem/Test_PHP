USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF  EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SendEmails'
          AND Object_ID = Object_ID(N'[dbo].[RequestTypes]'))
BEGIN
 ALTER TABLE RequestTypes ALTER COLUMN SendEmails INT
END

GO