USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

      IF NOT  EXISTS (SELECT 1 FROM sys.objects 
      WHERE object_id = OBJECT_ID(N'[dbo].[PHLExpiryStartDate]'))
      BEGIN
                  CREATE TABLE [dbo].[PHLExpiryStartDate](
                        [PHLExpiryStartDate] [varchar](50) Not NULL
                  )
                  
      END

      IF EXISTS(SELECT 1 FROM sys.columns 
            WHERE Name = N'PHLExpiryStartDate'
            AND Object_ID = Object_ID(N'[dbo].[PHLExpiryStartDate]'))
      BEGIN
      INSERT INTO PHLExpiryStartDate (PHLExpiryStartDate) VALUES('2021-04-01');
      END

GO



