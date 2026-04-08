USE [Allocate7]
GO

/****** Object:  Table [dbo].[adhoc_duty]    Script Date: 26/08/2024  ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsNeedCovering'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
BEGIN
    alter table adhoc_duty add IsNeedCovering BIT NULL;
END;

GO