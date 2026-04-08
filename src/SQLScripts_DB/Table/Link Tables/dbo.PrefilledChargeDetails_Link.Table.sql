USE [Allocate7]
GO

/****** Object:  Table [dbo].[PrefilledChargeDetails_Link]    Script Date: 13/01/2022 18:21:37 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF  NOT EXISTS (SELECT 1 FROM sys.objects
WHERE object_id = OBJECT_ID(N'[dbo].[PrefilledChargeDetails_Link]'))
BEGIN

CREATE TABLE [dbo].[PrefilledChargeDetails_Link](
    [PrefilledId] [int] IDENTITY(1,1) NOT NULL,
    [ChargingId] [int] NOT NULL,
    [IsPrefilled] [bit] NOT NULL default(0),
    [UserId] [int] NOT NULL,
    [CreatedDate] [datetime] NULL,
    CONSTRAINT [PK_PrefilledChargeDetails_Link] PRIMARY KEY CLUSTERED
(
[PrefilledId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
    ) ON [PRIMARY]
END
GO
