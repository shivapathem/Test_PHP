USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'MasterDutyId' AND Object_ID = Object_ID(N'[dbo].[Allocations_edit]'))
BEGIN
    ALTER TABLE Allocations_edit ADD MasterDutyId INT
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'AdhocDuty' AND Object_ID = Object_ID(N'[dbo].[Allocations_edit]'))
BEGIN
    ALTER TABLE Allocations_edit ADD AdhocDuty INT
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'MarkedOvertime' AND Object_ID = Object_ID(N'[dbo].[Allocations_edit]'))
BEGIN
    ALTER TABLE Allocations_edit ADD MarkedOvertime INT
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'MarkedSickness' AND Object_ID = Object_ID(N'[dbo].[Allocations_edit]'))
BEGIN
    ALTER TABLE Allocations_edit ADD MarkedSickness INT
END

GO
