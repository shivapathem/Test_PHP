USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_getMasterColorValueByColorId]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_getMasterColorValueByColorId]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_getMasterColorValueByColorId] 
	-- Add the parameters for the stored procedure here
	@ColorId int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	SELECT ColourBackground,ColourFont FROM REF_MasterDutyColours WHERE MasterDutyColourID = @ColorId


END'
EXEC dbo.sp_executesql @strSQL

GO
