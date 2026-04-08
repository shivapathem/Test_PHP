USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS(SELECT 1 FROM sys.columns WHERE Name = N'IsActual' AND Object_ID = Object_ID(N'[dbo].[ChargingDutyMapping_Link]'))
BEGIN
ALTER TABLE ChargingDutyMapping_Link ALTER COLUMN IsActual INT
END
 

GO
