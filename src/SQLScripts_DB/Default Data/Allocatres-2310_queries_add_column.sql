USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'IsPublic'
          AND Object_ID = Object_ID(N'[dbo].[MasterDutiesFilter]'))
BEGIN
    ALTER TABLE MasterDutiesFilter ADD IsPublic TinyInt DEFAULT 0 
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedDate'
          AND Object_ID = Object_ID(N'[dbo].[MasterDutiesFilter]'))
BEGIN
    ALTER TABLE MasterDutiesFilter ADD CreatedDate DateTime 
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedBy'
          AND Object_ID = Object_ID(N'[dbo].[MasterDutiesFilter]'))
BEGIN
    ALTER TABLE MasterDutiesFilter ADD CreatedBy INT 
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'UpdateDate'
          AND Object_ID = Object_ID(N'[dbo].[MasterDutiesFilter]'))
BEGIN
    ALTER TABLE MasterDutiesFilter ADD UpdateDate DateTime 
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'UpdatedBy'
          AND Object_ID = Object_ID(N'[dbo].[MasterDutiesFilter]'))
BEGIN
    ALTER TABLE MasterDutiesFilter ADD UpdatedBy INT 
END


GO
