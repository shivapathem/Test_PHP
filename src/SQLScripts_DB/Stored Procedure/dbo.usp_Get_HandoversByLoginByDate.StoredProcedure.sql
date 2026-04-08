USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_Get_HandoversByLoginByDate]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_Get_HandoversByLoginByDate]
	-- Add the parameters for the stored procedure here
	@intschedulingTeamId int,
	@strDate DATE
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	SELECT * FROM handovers
    WHERE  messagedate = @strDate
       AND deleted = 0
    AND schedulingTeamId = @intschedulingTeamId
    ORDER BY posttime
END
'

EXEC dbo.sp_executesql @strSQL

GO