USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

 IF NOT  EXISTS (SELECT 1 FROM sys.objects 
    WHERE object_id = OBJECT_ID(N'[dbo].[WTD_Types]'))
  BEGIN
		CREATE TABLE [dbo].[WTD_Types](
			[ID] [smallint] NULL,
			[Rule] [varchar](30) NULL,
			[Description] [varchar](100) NULL
		) ON [PRIMARY]
		
  END

GO

IF NOT EXISTS(SELECT 1 FROM WTD_Types where ID = 1 )
BEGIN
      INSERT INTO WTD_Types ( ID,"Rule", Description)
      VALUES(1,'11 Hour Break Rule','The break between 2 consecutive shifts is less than 11 hours')
END

IF NOT EXISTS(SELECT 1 FROM WTD_Types where ID = 2 )
BEGIN
      INSERT INTO WTD_Types ( ID,"Rule", Description)
      VALUES(2,'2 Days Off in 14 Rule','The person should have at least 2 days off in any 14 day period')
END

IF NOT EXISTS(SELECT 1 FROM WTD_Types where ID = 3 )
BEGIN
      INSERT INTO WTD_Types ( ID,"Rule", Description)
      VALUES(3,'Ave 48 Hours per Week Rule','The person can work a maximum of 48 hours per Week averaged over any 17 week period')
END

IF NOT EXISTS(SELECT 1 FROM WTD_Types where ID = 4 )
BEGIN
      INSERT INTO WTD_Types ( ID,"Rule", Description)
      VALUES(4,'More than 6 Days Straight','Optional Test for a person working more than 6 days straight')
END