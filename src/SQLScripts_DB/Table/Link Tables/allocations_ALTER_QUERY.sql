USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyTeamID'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
 ALTER TABLE [dbo].[Allocations] ADD DutyTeamID INT NULL
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'SchedulingPersonID'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD SchedulingPersonID INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyDate'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD DutyDate DATETIME NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'StartDate'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD StartDate DATETIME NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'EndDate'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD EndDate DATETIME NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'isAttention'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD isAttention bit NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'isRequest'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD isRequest bit NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'aftermidnight'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD aftermidnight bit not null default(0)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'dutyProgramId'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD dutyProgramId INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'dutyBreakTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD dutyBreakTime INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'dutyColorId '
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD dutyColorId INT NULL DEFAULT 0
END

--created on 13/10/2021 -- Naveeta --
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'MannualOThours'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD MannualOThours INT  DEFAULT 0
END
GO
--created on 13/10/2021 -- Naveeta
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'isEdited'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD isEdited INT DEFAULT 0
END
-- 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsActive'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD IsActive INT DEFAULT 1
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'isEditable'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
Alter TABLE Allocations Add isEditable bit default 1
END

-- created on Fri Oct 29 2021 - cagri
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'MarkWTD'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD MarkWTD INT DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'WTDComments'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE dbo.[Allocations] ADD WTDComments VARCHAR(1000) DEFAULT NULL
END


GO

--created on 21/10/2021 -- Naveeta --
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'MasterDutyId'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD MasterDutyId INT  DEFAULT 0
END
GO
--created on 21/10/2021 -- Naveeta


--created on 21/10/2021 -- Naveeta --
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'isActiveDuty'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD isActiveDuty INT  DEFAULT 1
END
---Soniya for CompareEdit
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'isCompareEdited'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD isCompareEdited BIT
END


DECLARE @checkisCompareEditedStatus VARCHAR(10)

SET @checkisCompareEditedStatus = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='isCompareEdited'
AND    object_id = object_id('dbo.Allocations'))
IF(ISNULL(@checkisCompareEditedStatus,'' )= '')
BEGIN
    ALTER TABLE Allocations ADD DEFAULT(0) FOR isCompareEdited
END
GO
