USE [BBCSchedules]
GO

SET ANSI_NULLS ON
GO 

SET QUOTED_IDENTIFIER ON
GO 

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'UserID'
          AND Object_ID = Object_ID(N'[dbo].[skills_programmes_staff_link]'))
BEGIN
ALTER TABLE skills_programmes_staff_link ADD UserID INT
END

GO