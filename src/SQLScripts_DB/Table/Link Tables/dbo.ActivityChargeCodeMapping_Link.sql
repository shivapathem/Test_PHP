USE [Allocate7]
GO
/****** Object:  Table [dbo].[ActivityChargeCodeMapping_Link]    Script Date: 31/12/2021 20:15:26 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT object_id FROM sys.tables WHERE name in ('ActiveChargeCodeMapping_Link','ActivityChargeCodeMapping_Link'))
BEGIN
CREATE TABLE [dbo].[ActivityChargeCodeMapping_Link](
    [MappingId] [int] IDENTITY(1,1) NOT NULL,
    [Year] [int] NOT NULL,
    [EffectiveFrom] [date] NOT NULL,
    [EstablishCodeId] [int] NOT NULL,
    [ActiveCodeId] [int] NOT NULL,
    [CreatedDate] [datetime] NULL,
    [CreatedBy] [int] NULL,
    [UpdatedDate] [datetime] NULL,
    [UpdateBy] [int] NULL,
    [Price] [decimal](18,0) NULL,
    CONSTRAINT [PK_ActivityChargeCodeMapping_Link] PRIMARY KEY CLUSTERED
(
[MappingId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
    ) ON [PRIMARY]

END
GO
