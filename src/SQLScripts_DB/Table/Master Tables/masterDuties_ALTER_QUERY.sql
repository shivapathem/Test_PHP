USE [Allocate7]
GO

/****** Object:  Table [dbo].[MasterDuties]    Script Date: 13/08/2021  ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'BreakTime'
          AND Object_ID = Object_ID(N'MasterDuties'))
BEGIN
    alter table MasterDuties add BreakTime numeric(9);
END;
GO