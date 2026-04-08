USE [Allocate7]
GO

/****** Object:  Table [dbo].[LeaveApplications]    Script Date: 03/09/2024  ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsAgreed'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
    ALTER TABLE LeaveApplications ADD IsAgreed BIT NULL;
END;

GO