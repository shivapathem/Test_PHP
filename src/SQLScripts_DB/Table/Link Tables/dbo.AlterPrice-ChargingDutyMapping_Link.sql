USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'Quantity' AND Object_ID = Object_ID(N'[dbo].[ChargingDutyMapping_Link]'))

BEGIN

	ALTER TABLE ChargingDutyMapping_Link ALTER COLUMN Quantity DECIMAL(18, 2)

END
