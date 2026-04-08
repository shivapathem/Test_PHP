USE [Allocate7]
GO
/****** Object:  Table [dbo].[ScheduledPersonTeam_LINK]    Script Date: 28/03/2023 20:50:34 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsDefaultBGColour' AND Object_ID = Object_ID(N'[dbo].[ScheduledPersonTeam_LINK]'))
BEGIN
    ALTER TABLE ScheduledPersonTeam_LINK ADD DEFAULT(0) FOR IsDefaultBGColour;
END

BEGIN
    UPDATE ScheduledPersonTeam_LINK SET IsDefaultBGColour = 0;
END

GO
SET ANSI_PADDING OFF
GO