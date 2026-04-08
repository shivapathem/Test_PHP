USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_create_update_allocationsTextColours]    Script Date: 07/06/2024 02:22:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_create_update_allocationsTextColours]
@IntId INT = 0,
@Description NVARCHAR(50),
@TextColour NVARCHAR(50),
@DivisionID INT

AS
BEGIN
  IF (@IntId = 0) 
  BEGIN
    INSERT INTO   AllocationsTextColours
			(Description, TextColour, DivisionID)
              VALUES (@Description, @TextColour, @DivisionID)
  END
  ELSE
  BEGIN
  UPDATE AllocationsTextColours
              SET Description = @Description, DivisionID = @DivisionID, TextColour = @TextColour
              WHERE  (ID = @IntId)
	END
END